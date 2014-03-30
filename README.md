# Permissions Component

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
public function canCreate($options) {
    $result = false;
    
    switch($options['PermissibleModel']) {
        case 'PermAccount':
            //For testing purposes allow only ID 1 to create
            if(isset($options['PermissibleId']) && $options['PermissibleId'] == 1) {
                $result = true;
            }
        break; 
    }
    return $result;
}
```

Will result that the user logged in with id 1 to have access to this model, it is possible to do authentication with multiple models, when, for example, you also want to define permissions to something like an api key. You only need to change the userModel used by the Authentication component

## Admin users

For convenience you can define an "isAdmin" method in your user model, when the method is defined the permissions component will always first poll this method. If the method returns true, it will allow the authenticated user to do everything.