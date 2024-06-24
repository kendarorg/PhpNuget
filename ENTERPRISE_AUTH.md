# Enterprise Authentication

Adding Enterprise Authentication to the management interface for PhpNuget is simple now.
The premise is that the web server passes an environmental variable with the already authenticated user to the server.
This approach has the benefit of being very flexible without adding complexity to the code.

## What DOES change

Passwords within the application do not matter for the web Management interface. They would still be used for API functions.

## What does NOT change

Pretty much everything else. Users are still managed within PhpNuget. The environmental variable must point to a value that is defined in either the username or email fields. All of the API calls are still the same.

## PhpNuget Setting

Simply define the environmental variable that you want to use for the management as shown below.

```
@define('__ENTERPRISE_AUTH_ENV__', "REMOTE_USER");
```

## Web Server Settings

Add a location block to your configuration for the server directory index.php file. This is only to give you an idea of what you need to do on the web server side. You will have to follow your web server documentation to set this up.

```
<Location /phpnuget/entrprise >
    AuthLDAPURL "ldap://ldap1.example.com:389/ou=People, o=Example?uid?sub?(objectClass=*)"
    Require valid-user
</Location>
```

## Important Setup Note

You will have to add your initial entrprise user account using the regular method.