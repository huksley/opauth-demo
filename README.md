Opauth-Demo
=============
Opauth strategy for testing authentication.
Opauth is a multi-provider authentication framework for PHP.

Getting started
----------------
1. Install Opauth-Demo:
   ```bash
   cd path_to_opauth/Strategy
   git clone http://github.com/huksley/opauth-demo Demo
   ```

2. Configure Opauth-GitHub strategy with `username`, `password` and `email`.

3. Direct user to `http://path_to_opauth/demo` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Demo' => array(
	'username' => 'Username to auth with',
	'password' => 'Password to auth with',
	'email' => 'E-mail this account gets'
)
```

License
---------
Opauth-Demo is MIT Licensed  
Copyright © 2014 Huksley
