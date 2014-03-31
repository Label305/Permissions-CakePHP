# Permissions Component [![Build Status](https://travis-ci.org/Label305/CakePHP-Permissions-Component.svg?branch=master)](https://travis-ci.org/Label305/CakePHP-Permissions-Component)

Eases up fetching CRUD permissions from models.

## Install

Add the repository to your requirements and load using composer

```
    "require": {
        "label305/permissions-component": "dev-master"
    }
```

Add to your app's components

```
public $components = array(
	'PermissionsComponent.Permissions'
);
```

## Usage

Setup authentication using the Auth component and define a user model

```
public $components = array(
    'Auth' => array(
        'authenticate' => array(
            'Form' => array(
                'userModel' => 'YourUserModel'
                )
            )
        )
    );
```

After that the Permissions component will use the YourUserModel as the PermissibleModel passed in the options to your CRUD methods.

For example:

```
public function canUpdate($options) {
    $result = false;
    
    switch($options['PermissibleModel']) {
        case 'PermAccount':
            if(isset($options['PermissibleId'], $options['id'])) {
				$this->id = $options['id'];
                $result = $this->field('perm_account_id') == $options['PermissibleId'];
            }
        break; 
    }
    return $result;
}
```

This will check if the requested item with ```$options['id']``` has the ```perm_account_id``` matching to the logged in user. It is possible to do authentication with multiple models, when, for example, you also want to define permissions to something like an api key. You only need to change the userModel used by the Authentication component

## Admin users

For convenience you can define an "isAdmin" method in your user model, when the method is defined the permissions component will always first poll this method. If the method returns true, it will allow the authenticated user to do everything.