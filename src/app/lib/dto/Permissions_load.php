<?php

class Permissions
{
    var $i;
    var $permission;

    public function __construct($i,$permission)
    {
        $this->i = $i;
        $this->permission = $permission;
    }

    private function prR($i)
    {
        return strpos($i, 'R') !== false || strpos($i, 'C') !== false || strpos($i, 'U') !== false || strpos($i, 'D') !== false;
    }

    private function prRO($i)
    {
        return strpos($i, 'O') !== false || $this->prR($i);
    }

    private function prC($i)
    {
        return strpos($i, 'R') !== false || strpos($i, 'C') !== false;
    }

    private function prCO($i)
    {
        return strpos($i, 'O') !== false || $this->prC($i);
    }

    private function prD($i)
    {
        return strpos($i, 'D') !== false;
    }

    private function prDO($i)
    {
        return strpos($i, 'O') !== false || $this->prD($i);
    }

    private function prU($i)
    {
        return strpos($i, 'U') !== false;
    }

    private function prUO($i)
    {
        return strpos($i, 'O') !== false || $this->prU($i);
    }

    public function canRead()
    {
        return $this->prR($this->i);
    }

    public function canReadOwn()
    {
        return $this->prRO($this->i);
    }

    public function canCreate()
    {
        return $this->prC($this->i);
    }

    public function canCreateOwn()
    {
        return $this->prCO($this->i);
    }

    public function canDelete()
    {
        return $this->prD($this->i);
    }

    public function canDeleteOwn()
    {
        return $this->prDO($this->i);
    }

    public function canUpdate()
    {
        return $this->prU($this->i);
    }

    public function canUpdateOwn()
    {
        return $this->prUO($this->i);
    }

    public function limited()
    {
        return $this->prR($this->i) || strpos($this->i, 'L') !== false;
    }



    public function limitedOwn()
    {
        return strpos($this->i, 'X') !== false;
    }

    private function denyUnauthorized()
    {
        $sender = GlobalRegistry::get('Sender');
        $sender->sendErrorResponse(translate("NOT_AUTHORIZED"), 401);
    }

    public function canReadThrow()
    {
        if (!$this->canRead()) $this->denyUnauthorized();
    }

    public function canReadOwnThrow()
    {
        if (!$this->canReadOwn()) $this->denyUnauthorized();
    }

    public function canCreateThrow()
    {
        if (!$this->canCreate()) $this->denyUnauthorized();
    }

    public function canCreateOwnThrow()
    {
        if (!$this->canCreateOwn()) $this->denyUnauthorized();
    }

    public function canDeleteThrow()
    {
        if (!$this->canDelete()) $this->denyUnauthorized();
    }

    public function canDeleteOwnThrow()
    {
        if (!$this->canDeleteOwn()) $this->denyUnauthorized();
    }

    public function canUpdateThrow()
    {
        if (!$this->canUpdate()) $this->denyUnauthorized();
    }

    public function canUpdateOwnThrow()
    {
        if (!$this->canUpdateOwn()) $this->denyUnauthorized();
    }

    public function limitedThrow()
    {
        if (!$this->limited()) $this->denyUnauthorized();
    }
    public function value()
    {
        return $this->i;
    }

    public function canAccess()
    {
        return strlen($this->value())>0;
    }
}
