<?php
require_once("../config.inc");
class LoginApi extends BaseApis{


    function apiCallLogout(){
        $this->auth->logoutUser();
        $this->sender->sendSuccessResponse();
    }

    function apiCallLogin(){
        try {
            $login = $this->getParamOrDefault('login');
            if(!$this->auth->checkUser($login)){
                $this->audit->auditError("LOGIN_RETRY", "USER", $login);
                throw new Exception("Invalid username or password");
            }
            $password = $this->getParamOrDefault('password');
            if($this->auth->authenticateUser($login, $password)){
                $this->auth->unlockUser($login);
                $this->audit->audit("LOGIN", "USER", [
                    'id' => $login,
                    'source'=>$this->audit->findPossibleIps(),
                    'userAgent'=> $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                $this->sender->sendSuccessResponse(translate("OPERATION_SUCCESSFUL"));
            }else {
                $this->audit->auditError("LOGIN_INVALID", "USER", $login);
                throw new Exception("Invalid username or password");
            }
        }catch (Exception){
            $this->sendUnauthorizedResponse(translate("NOT_AUTHORIZED"));
        }
    }

    function apiCallResetPassword() {
        $login = $this->getParamOrDefault('login');
        try {
            if (!$login) {
                $this->sender->sendSuccessResponse();
                return;
            }

            $db = getDbConnection();
            $db->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");

            $stmt = $db->prepare("SELECT id, email, username FROM users WHERE username = ? AND locked = 0");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->audit->auditError("RESET_PASS_REQUESTED", "USERS", $login);
                $this->sender->sendSuccessResponse();
                return;
            }

            if (empty($user['email'])) {
                $this->audit->auditError("RESET_PASS_MISS_MAIL", "USERS", $login);
                $this->sender->sendSuccessResponse();
                return;
            }

            $token   = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);

            $resetLink = HOST_PATH . "/ui/reset_password.html"
                . "?token=" . urlencode($token)
                . "&login=" . urlencode($user['username']);

            $wrapper = new PhpMailWrapper();
            $mail = $wrapper->mailer;
            $mail->addAddress($user['email']);
            $mail->addCC(SUPPORT_MAIL);
            $mail->Subject = translate("RESET_PASSWORD") . " - " . $user['username'];
            $mail->Body =
                "E' stato richiesto il reset della password per l'utente "
                . $user['username'] . ".\n\n"
                . "Apri il link seguente per impostare una nuova password:\n"
                . $resetLink . "\n\n"
                . "Il link scadra' il " . $expires . ".\n";
            $mail->AltBody = $mail->Body;
            $mail->send();

            if ($mail->isError()) {
                $this->audit->auditError("RESET_PASS_REQUESTED", "USERS", [
                    'user'  => $user['username'],
                    'error' => $mail->ErrorInfo,
                ]);
            } else {
                $this->audit->audit("RESET_PASS_REQUESTED", "USERS", [
                    'user'    => $user['username'],
                    'expires' => $expires,
                ]);
            }

            $this->sender->sendSuccessResponse();
        } catch (Exception $e) {
            $this->log->error("Error sending reset link", $e);
            $this->audit->auditError("RESET_PASS_REQUESTED", "USERS", [
                'login' => $login,
                'error' => $e->getMessage(),
            ]);
            $this->sender->sendSuccessResponse();
        }
    }

    function apiCallResetPasswordConfirm() {
        try {
            $data     = $this->getJsonPostData();
            $login    = $data['login']    ?? null;
            $token    = $data['token']    ?? null;
            $password = $data['password'] ?? null;

            if (!$login || !$token || !$password) {
                $this->audit->auditError("RESET_PASS_CONFIRM", "USERS", "MISSING_INPUT");
                $this->sender->sendErrorResponse(translate("ERROR_GENERIC"), 400);
                return;
            }

            $db = getDbConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND locked = 0");
            $stmt->execute([$login]);
            $user = $stmt->fetch();
            if (!$user) {
                $this->audit->auditError("RESET_PASS_CONFIRM", "USERS", "USER_NOT_FOUND:$login");
                $this->sender->sendErrorResponse(translate("ERROR_GENERIC"), 400);
                return;
            }

            $stmt = $db->prepare("
                SELECT id FROM password_resets
                WHERE token = ? AND user_id = ? AND used = 0 AND expires_at > NOW()
                ORDER BY expires_at DESC LIMIT 1
            ");
            $stmt->execute([$token, $user['id']]);
            $reset = $stmt->fetch();
            if (!$reset) {
                $this->audit->auditError("RESET_PASS_CONFIRM", "USERS", "INVALID_TOKEN:$login");
                $this->sender->sendErrorResponse(translate("ERROR_GENERIC"), 400);
                return;
            }

            $usersModel = GlobalRegistry::get("UsersModel");
            $usersModel->updatePassword($user['id'], $password);

            $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset['id']]);

            $this->audit->audit("RESET_PASS_CONFIRM", "USERS", ['user' => $login]);
            $this->sender->sendSuccessResponse(translate("OPERATION_SUCCESSFUL"));
        } catch (Exception $e) {
            $this->log->error("Error confirming reset", $e);
            $this->sender->sendErrorResponse(translate("ERROR_GENERIC"), 500);
        }
    }

    function sendUnauthorizedResponse($message = 'Unauthorized access')
    {
        $this->sender->sendErrorResponse($message, 401);
    }
}

$api = new LoginApi();
$api->handle();
