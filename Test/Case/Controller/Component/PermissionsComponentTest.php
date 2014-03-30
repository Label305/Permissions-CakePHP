<?php
App::uses('Controller', 'Controller');
App::uses('Model', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('PermissionsComponent', 'PermissionsComponent.Controller/Component');
App::uses('AuthComponent', 'Controller/Component');

//Dummy model
class PermAccount extends Model {

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

}

// A fake controller to test against
class TestPermissionsController extends Controller {
    public $paginate = null;

    public $components = array(
        'Auth' => array(
            'authenticate' => array(
                'Form' => array(
                    'userModel' => 'PermAccount'
                    )
                )
            )
        );
}

class PermissionsComponentTest extends CakeTestCase {
    public $PermissionsComponent = null;
    public $Controller = null;

    /**
     * Fixtures
     * @var array
     */
    public $fixtures = array(
        'plugin.PermissionsComponent.PermAccount'
    );

    /**
     * Helper function to initialize controllers, will set $this->Controller
     * @param  String $controller
     * @return void
     */
    public function initController($controllerName) {
        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        $this->Controller = new $controllerName($CakeRequest, $CakeResponse);

        $this->Controller->constructClasses();
        $this->Controller->startupProcess();
    }

    /**
     * Helper function to intialize components, will set $this->{$componentName}
     * @param  String $componentName 
     * @return void
     */
    public function initComponent($componentName) {
        $Collection = new ComponentCollection();
        $this->{$componentName} = new $componentName($Collection);

        $this->{$componentName}->startup($this->Controller);
        $this->{$componentName}->initialize($this->Controller);
    }


    public function setUp() {
        parent::setUp();

        Configure::write('Security.salt', 'kuuz1AsivkTT0y34gfkh5jv5hkkhhgwwwLJJ2tlC');
        Configure::write('Security.cipherSeed', '06287392835928375698762345207');
            
        $this->initController('TestPermissionsController');
        $this->initComponent('PermissionsComponent');
    }

    public function testNormalizeOptions() {
        $this->Controller->Auth->logout();

        //Normalize not logged in
        $result = $this->PermissionsComponent->_normalizeOptions(array()); 
        $expected = array(
            'PermissibleModel' => 'PermAccount',
            'PermissibleId' => null
            );
        $this->assertEquals($result, $expected, 'Unexpected result from not-logged in normalization');

        //Normalize logged in
        $this->Controller->Auth->logout();
        $result = $this->Controller->Auth->login(array(
            'id' => 3,
            'email' => 'foo@bar.com',
            'password' => 'blub'
            ));
        $this->assertTrue($result !== false, 'Login failed');
        $result = $this->PermissionsComponent->_normalizeOptions(array()); 
        $expected = array(
            'PermissibleModel' => 'PermAccount',
            'PermissibleId' => 3
            );
        $this->assertEquals($result, $expected, 'Unexpected result from not-logged in normalization');
       
       $this->Controller->Auth->logout();

       //Check if settings are reset
        $result = $this->PermissionsComponent->_normalizeOptions(array()); 
        $expected = array(
            'PermissibleModel' => 'PermAccount',
            'PermissibleId' => null
            );
        $this->assertEquals($result, $expected, 'Unexpected result from not-logged in normalization');

        //Check if 'setPermissibleModel' will overwrite the other settings
        $this->PermissionsComponent->setPermissibleModel('User');
        $result = $this->PermissionsComponent->_normalizeOptions(array()); 
        $expected = array(
            'PermissibleModel' => 'User',
            'PermissibleId' => null
            );
        $this->assertEquals($result, $expected, 'Unexpected result from not-logged in normalization');

        //Check if 'setPermissibleId' will take effect
        $this->PermissionsComponent->setPermissibleId(7);
        $result = $this->PermissionsComponent->_normalizeOptions(array()); 
        $expected = array(
            'PermissibleModel' => 'User',
            'PermissibleId' => 7
            );
        $this->assertEquals($result, $expected, 'Unexpected result from not-logged in normalization');
    }

    public function testGetRights() {
        //Might still be logged in as an adin
        $this->Controller->Auth->logout();

        //Virtual model (defaults to false)
        $result = $this->PermissionsComponent->_getRights('canCreate', 'FooBar');
        $this->assertFalse($result, 'Invalid rights for canCreate on virtual model');
        $result = $this->PermissionsComponent->_getRights('canRead', 'FooBar');
        $this->assertFalse($result, 'Invalid rights for canRead on virtual model');
        $result = $this->PermissionsComponent->_getRights('canUpdate', 'FooBar');
        $this->assertFalse($result, 'Invalid rights for canUpdate on virtual model');
        $result = $this->PermissionsComponent->_getRights('canDelete', 'FooBar');
        $this->assertFalse($result, 'Invalid rights for canDelete on virtual model');

        //Logged in as an PermAccount
        $this->Controller->Auth->logout();
        $this->Controller->Auth->login(array(
            'id' => 1
            ));
        $result = $this->PermissionsComponent->_getRights('canCreate', 'PermAccount');
        $this->assertTrue($result, 'Invalid rights for canCreate on PermAccount');
    }

    public function tearDown() {
        parent::tearDown();
        // Clean up after we're done
        unset($this->PermissionsComponent);
        unset($this->Controller);
    }
}