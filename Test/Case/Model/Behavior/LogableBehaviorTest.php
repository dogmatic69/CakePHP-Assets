<?php

class LogableBook extends CakeTestModel {

	public $actsAs = array(
		'CakePHPAssets.Logable' => array('userModel' => 'LogableUser'),
		'CakePHPAssets.Ordered' => array('foreign_key' => false)
	);

	public $order = 'weight ASC';

}

class Log extends CakeTestModel {

	public $order = 'id DESC';

	public $fixture = 'logable_log';

	public $useTable = 'logable_logs';

	public function find($command, $options = array()) {
		if ($command == 'last') {
			$options = array_merge(array('order' => 'id DESC'), $options);
			return parent::find('first', $options);
		} else {
			return parent::find($command, $options);
		}
	}

}

class LogableUser extends CakeTestModel {

	public $name = 'LogableUser';

	public $actsAs = array('Logable' => array(
		'userModel' => 'LogableUser',
		'ignore' => array('counter')
	));

}

class LogableComment extends CakeTestModel {

	public $name = 'LogableComment';

	public $actsAs = array('Logable' => array('userModel' => 'LogableUser'));

}

class LogableCase extends CakeTestCase {

	public $BookTest = NULL;

	public $Log = NULL;

	public $fixtures = array(
		'plugin.cake_p_h_p_assets.logable_log', 
		'plugin.cake_p_h_p_assets.logable_book', 
		'plugin.cake_p_h_p_assets.logable_user', 
		'plugin.cake_p_h_p_assets.logable_comment'
	);

	public function startTest() {		
		$this->LogableBook = ClassRegistry::init('LogableBook');
		$this->Log = ClassRegistry::init('Log');
		$this->LogableUser = ClassRegistry::init('LogableUser');
		$this->LogableComment = ClassRegistry::init('LogableComment');
	}

	public function endTest() {
		unset($this->LogableBook);
		unset($this->Log);
		unset($this->LogableUser);
		unset($this->LogableComment);
		ClassRegistry::flush();
	}

	public function testFindLog() {
		$result = $this->LogableBook->findLog(array('order' => 'id DESC'));
		$expected = array( 
			array(
				'Log' => array(
					'id' => 5,
					'title' => 'New Book',
					'description' =>  'LogableBook "New Book" (7) added by LogableUser "Steven" (301).',
					'model' => 'LogableBook',
					'model_id' => 7,
					'action' => 'add',
					'user_id' => 301,
					'change' => 'title' 	
				)
			),	
			array(
				'Log' => array(
					'id' => 4,
					'title' => 'Fifth Book',
					'description' =>  'LogableBook "Fifth Book" (6) deleted by LogableUser "Alexander" (66).',
					'model' => 'LogableBook',
					'model_id' => 6,
					'action' => 'delete',
					'user_id' => 66,
					'change' => '' 	
				)
			),   
			array(
				'Log' => array(
					'id' => 2,
					'title' => 'Fifth Book',
					'description' =>  'LogableBook "Fifth Book" (6) updated by LogableUser "Alexander" (66).',
					'model' => 'LogableBook',
					'model_id' => 6,
					'action' => 'edit',
					'user_id' => 66,
					'change' => 'title' 	
				)  
			),  
			array(
				'Log' => array(
					'id' => 1,
					'title' => 'Fifth Book',
					'description' =>  'LogableBook "Fifth Book" (6) created by LogableUser "Alexander" (66).',
					'model' => 'LogableBook',
					'model_id' => 6,
					'action' => 'add',
					'user_id' => 66,
					'change' => 'title' 	
				)
			) 	
		);
		$this->assertEquals($expected, $result);
		
		// asking for user, but not model, so should just get users changes on current model
		$expected = array( 
			array(
				'Log' => array(
					'id' => 5,
					'title' => 'New Book',
					'description' =>  'LogableBook "New Book" (7) added by LogableUser "Steven" (301).',
					'model' => 'LogableBook',
					'model_id' => 7,
					'action' => 'add',
					'user_id' => 301,
					'change' => 'title' 	
				)
			)
		);		
		$result = $this->LogableBook->findLog(array('user_id' => 301, 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array(
				'id' => 5,
				'title' => 'New Book',
				'description' =>  'LogableBook "New Book" (7) added by LogableUser "Steven" (301).',
				'model' => 'LogableBook',
				'model_id' => 7,
				'action' => 'add',
				'user_id' => 301,
				'change' => 'title' 	
			))
		);
		$result = $this->LogableBook->findLog(array('model_id' => 7, 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
				
		$expected = array(
			array('Log' => array('id' => 4)),
			array('Log' => array('id' => 2)),
			array('Log' => array('id' => 1))
		);
		$result = $this->LogableBook->findLog(array('model_id' => 6, 'fields' => array('id'), 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
		
		$expected = array(array('Log' => array('id' => 4)));
		$result = $this->LogableBook->findLog(array('action' => 'delete', 'fields' => array('id'), 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array('id' => 5)),
			array('Log' => array('id' => 1))
		);
		$result = $this->LogableBook->findLog(array('action' => 'add', 'fields' => array('id'), 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
		
		$expected = array(array('Log' => array('id' => 2)));
		$result = $this->LogableBook->findLog(array('action' => 'edit', 'fields' => array('id'), 'order' => 'id DESC'));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array('id' => 5)),
			array('Log' => array('id' => 1))
		);
		$result = $this->LogableBook->findLog(array(
			'action' => 'add',
			'fields' => array('id'), 
			'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array('id' => 5)),
			array('Log' => array('id' => 1))
		);		
		$result = $this->LogableBook->findLog(array(
			'action' => 'add',
			'fields' => array('id'),
			'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);

		$expected = array(array('Log' => array('id' => 4)));
		$result = $this->LogableBook->findLog(array(
			'fields' => array('id'),
			'conditions' => array('user_id' < 300, 'action' => 'delete'), 
			'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);				
	}
	
	public function testFindLogMoreModels() {
		// all actions of user Steven
		$expected = array(array('Log' => array('id' => 5)), array('Log' => array('id' => 3)));
		$result = $this->LogableBook->findLog(array(
			'fields' => array('id'),
			'user_id' => 301,
			'model' => false, 
			'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);
		
		// all delete actions of user Alexander
		$expected = array(array('Log' => array('id' => 4)));
		$result = $this->LogableBook->findLog(array(
			'fields' => array('id'),
			'user_id' => 66,
			'action' => 'delete',
			'model' => false,
		   'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);
		
		// get a differnt models logs
		$expected = array(array('Log' => array('id' => 3)));
		$result = $this->LogableBook->findLog(array(
			'fields' => array('id'),
			'order' => 'id ASC',
			'model' => 'LogableUser', 
			'order' => 'id DESC'
		));
		$this->assertEqual($expected, $result);
		
	}

	public function testFindUserActions() {		
		$expected = array(
			array('Log' => array('id' => 5)),
			array('Log' => array('id' => 3))
		);		
		$result = $this->LogableBook->findUserActions(301, array('fields' => 'id'));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array('id' => 4, 'event' => 'Alexander deleted the logablebook(id 6)')),
			array('Log' => array('id' => 2, 'event' => 'Alexander edited title of logablebook(id 6)')),
			array('Log' => array('id' => 1, 'event' => 'Alexander added a logablebook(id 6)'))
		);		
		$result = $this->LogableBook->findUserActions(66, array('events' => true));
		$this->assertEqual($expected, $result);
		
		$expected = array(
			array('Log' => array('id' => 5))
		);		
		$result = $this->LogableBook->findUserActions(301, array('fields' => 'id', 'model' => 'LogableBook'));
		$this->assertEqual($expected, $result);
		
	}

	public function testAddingModels() {
		$this->LogableBook->save(array('LogableBook' => array('title' => 'Denver')));
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Denver',
				'description' => 'LogableBook "Denver" (7) added by System.',
				'model' => 'LogableBook',
				'model_id' => 7,
				'action' => 'add',
				'user_id' => 0,
				'change' => 'title, weight',
			)		
		);
		
		// check with user
		$this->assertEquals($expected, $result);
		$this->LogableBook->create();
		$this->LogableBook->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableBook->save(array('LogableBook' => array('title' => 'New Orleans')));
		$this->LogableBook->clearUserData();
		$result = $this->Log->find('last');
				
		$expected = array('Log' => array(
			'id' => 7,
			'title' => 'New Orleans',
			'description' => 'LogableBook "New Orleans" (8) added by LogableUser "Alexander" (66).',
			'model' => 'LogableBook',
			'model_id' => 8,
			'action' => 'add',
			'user_id' => 66, 
			'change' => 'title, weight',
			)		
		);
		$this->assertEqual($expected, $result);
	}
	
	public function testEditingModels() {
		$data = array('LogableBook' => array('id' => 5, 'title' => 'Forth book')); 
		$this->LogableBook->save($data, false);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Forth book',
				'description' => 'LogableBook "Forth book" (5) updated by System.',
				'model' => 'LogableBook',
				'model_id' => 5,
				'action' => 'edit',
				'user_id' => 0,
				'change' => 'title',
			));
		$this->assertEquals($expected, $result);				
	}
	
	public function testDeletingModels() {
		$this->LogableBook->delete(5);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Fourth Book',
				'description' => 'LogableBook "Fourth Book" (5) deleted by System.',
				'model' => 'LogableBook',
				'model_id' => 5,
				'action' => 'delete',
				'user_id' => 0,
				'change' => '',
			));		
		$this->assertEqual($expected, $result);			
	}
	
	public function testUserLogging()  {
		$this->LogableUser->save(array('LogableUser' => array('name' => 'Jonny')));
		$result = $this->Log->find('first', array('id' => 6));
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Jonny',
				'description' => 'LogableUser "Jonny" (302) added by System.',
				'model' => 'LogableUser',
				'model_id' => 302,
				'action' => 'add',
				'user_id' => 0,
				'change' => 'name',
			)		
		);

		// check with LogableUser
		$this->assertEqual($expected, $result);
		$this->LogableUser->delete(302);	
		$result = $this->Log->find('first', array(
			'conditions' => array('id' => 7)
		));
		$expected = array(
			'Log' => array(
				'id' => 7,
				'title' => 'Jonny',
				'description' => 'LogableUser "Jonny" (302) deleted by System.',
				'model' => 'LogableUser',
				'model_id' => 302,
				'action' => 'delete',
				'user_id' => 0,
				'change' => '',
			)		
		);
		// check with LogableUser
		$this->assertEqual($expected, $result);
	}
	
	public function testLoggingWithoutDisplayField() {
		$this->LogableComment->save(array('LogableComment' => array('content' => 'You too?')));
		$result = $this->Log->find('first', array(
			'conditions' => array('Log.id' => 6)
		));
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'LogableComment (5)',
				'description' => 'LogableComment (5) added by System.',
				'model' => 'LogableComment',
				'model_id' => 5,
				'action' => 'add',
				'user_id' => 0,
				'change' => 'content',
			)		
		);
		$this->assertEquals($expected, $result);		
	}
		
	public function testConfigurationsWithoutDescription() {
		$this->markTestIncomplete('Cant mess with the schema');
		$description = $this->Log->_schema['description'];
		unset($this->Log->_schema['description']);		
		$this->LogableBook->create();
		$this->LogableBook->save(array('LogableBook' => array('title' => 'Denver')));
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Denver',
				'model' => 'LogableBook',
				'model_id' => 7,
				'action' => 'add',
				'user_id' => 0,
				'change' => 'title, weight',
			)		
		);
		$this->assertEqual($expected, $result);
		
		$data = array('LogableBook' => array('id' => 5, 'title' => 'Forth book')); 
		$this->LogableBook->save($data,false);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 7,
				'title' => 'Forth book',
				'model' => 'LogableBook',
				'model_id' => 5,
				'action' => 'edit',
				'user_id' => 0,
				'change' => 'title',
			));        			
		$this->assertEqual($expected, $result);
		
		$this->LogableBook->delete(5);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 8,
				'title' => 'Forth book',
				'model' => 'LogableBook',
				'model_id' => 5,
				'action' => 'delete',
				'user_id' => 0,
				'change' => '',
			));
					
		$this->assertEqual($expected, $result);		
		
		$this->Log->_schema['description'] = $description;
	}
	
	public function testConfigurationsWithoutModel() {
		$this->markTestIncomplete('Cant mess with the schema');
		$logSchema = $this->Log->_schema;
		unset($this->Log->_schema['description']);
		unset($this->Log->_schema['model']);		
		unset($this->Log->_schema['model_id']);		
		
		$this->LogableBook->create();
		$this->LogableBook->save(array('LogableBook' => array('title' => 'Denver')));
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'Denver',
				'action' => 'add',
				'user_id' => 0,
				'change' => 'title, weight',
			)		
		);
		$this->assertEqual($expected, $result);
		
		$data = array('LogableBook' => array('id' => 5, 'title' => 'Forth book')); 
		$this->LogableBook->save($data,false);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 7,
				'title' => 'Forth book',
				'action' => 'edit',
				'user_id' => 0,
				'change' => 'title',
			));        			
		$this->assertEqual($expected, $result);
		
		$this->LogableBook->delete(5);
		$result = $this->Log->find('last');
		$expected = array(
			'Log' => array(
				'id' => 8,
				'title' => 'Forth book',
				'action' => 'delete',
				'user_id' => 0,
				'change' => '',
			));
					
		$this->assertEqual($expected, $result);		
		
		$this->Log->_schema = $logSchema;
	}

	public function testConfiguratiosWithoutUserId() {
		$this->markTestIncomplete('Cant mess with the schema');
		$logSchema = $this->Log->_schema;
		unset($this->Log->_schema['user_id']);
		
		$this->LogableBook->create();
		$this->LogableBook->save(array('LogableBook' => array('title' => 'New Orleans')));
		
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 6,
				'title' => 'New Orleans',
				'description' => 'LogableBook "New Orleans" (7) added by System.',
				'model' => 'LogableBook',
				'model_id' => 7,
				'action' => 'add',
				'change' => 'title, weight',
			)		
		);				
		$this->assertEqual($expected, $result);		
		
		$this->LogableBook->create();
		$this->LogableBook->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableBook->save(array('LogableBook' => array('title' => 'New York')));
		$this->LogableBook->clearUserData();
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 7,
				'title' => 'New York',
				'description' => 'LogableBook "New York" (8) added by LogableUser "Alexander" (66).',
				'model' => 'LogableBook',
				'model_id' => 8,
				'action' => 'add',
				'change' => 'title, weight',
			)		
		);				
		$this->assertEqual($expected, $result);
		
		$this->Log->_schema = $logSchema;		
	}
	
	public function testConfiguratiosWithoutAction() {
		$this->markTestIncomplete('Cant mess with the schema');
		$logSchema = $this->Log->_schema;
		unset($this->Log->_schema['user_id']);
		
		$this->LogableBook->create();
		$this->LogableBook->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableBook->save(array('LogableBook' => array('title' => 'New Orleans')));
		$this->LogableBook->clearUserData();
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 6,
				'title' => 'New Orleans',
				'description' => 'LogableBook "New Orleans" (7) added by LogableUser "Alexander" (66).',
				'model' => 'LogableBook',
				'model_id' => 7,
				'action' => 'add',
				'change' => 'title, weight',
			)		
		);				
		$this->assertEqual($expected, $result);
		
		$this->LogableBook->delete(5);
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 7,
				'title' => 'Fourth Book',
				'description' => 'LogableBook "Fourth Book" (5) deleted by System.',
				'model' => 'LogableBook',
				'model_id' => 5,
				'action' => 'delete',
				'change' => '',
			)		
		);				
		$this->assertEqual($expected, $result);	
		
		$this->Log->_schema = $logSchema;		
	}	
		
	public function testConfiguratiosDefaults() {
		$this->markTestIncomplete('Cant mess with the schema');
		$logSchema = $this->Log->_schema;
		unset(
			$this->Log->_schema['user_id'], $this->Log->_schema['model'], $this->Log->_schema['model_id'],
			$this->Log->_schema['action'], $this->Log->_schema['change']
		);
		
		$this->LogableBook->create();
		$this->LogableBook->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableBook->save(array('LogableBook' => array('title' => 'New Orleans')));
		$this->LogableBook->clearUserData();
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 6,
				'title' => 'New Orleans',
				'description' => 'LogableBook "New Orleans" (7) added by LogableUser "Alexander" (66).',
			)		
		);				
		$this->assertEqual($expected, $result);
		
		$this->LogableBook->delete(5);
		$result = $this->Log->find('last');
		$expected = array('Log' => array(
				'id' => 7,
				'title' => 'Fourth Book',
				'description' => 'LogableBook "Fourth Book" (5) deleted by System.',
			)		
		);				
		$this->assertEqual($expected, $result);	
		
		$this->Log->_schema = $logSchema;		
	}

	public function testConfigurationWithoutMost() {
		$this->LogableComment->Behaviors->attach('Logable', array('description_ids' =>  false, 'userModel' => 'LogableUser'));
		$this->LogableComment->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableComment->save(array('LogableComment' => array('id' => 1, 'content' => 'You too?')));
		$result = $this->Log->find('first', array(
			'conditions' => array(
				'id' => 6
			)
		));
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'LogableComment (1)',
				'model' => 'LogableComment',
				'model_id' => 1,
				'action' => 'edit',
				'user_id' => 66,
				'change' => 'content',
				'description' => 'LogableComment updated by LogableUser "Alexander".',
			)		
		);
		$this->assertEqual($expected, $result);	
	}
	
	public function testIgnoreExtraFields() {
		$this->LogableComment->setUserData(array('LogableUser' => array('id' => 66, 'name' => 'Alexander')));
		$this->LogableComment->save(array('LogableComment' => array('id' => 1, 'content' => 'You too?', 'extra_field' => 'some data')));
		$result = $this->Log->find('first', array(
			'conditions' => array(
				'id' => 6
			)
		));
		$expected = array(
			'Log' => array(
				'id' => 6,
				'title' => 'LogableComment (1)',
				'description' => 'LogableComment (1) updated by LogableUser "Alexander" (66).',
				'model' => 'LogableComment',
				'model_id' => 1,
				'action' => 'edit',
				'user_id' => 66,
				'change' => 'content',
			)		
		);
		$this->assertEqual($expected, $result);		
	}

	public function testIgnoreSetup() {
		$log_rows_before = $this->Log->find('count', array('conditions' => array('model' => 'LogableUser', 'model_id' => 301)));		
		$this->LogableUser->save(array('id' => 301, 'counter' => 3));
		$log_rows_after = $this->Log->find('count', array('conditions' => array('model' => 'LogableUser', 'model_id' => 301)));	
		$this->assertEqual($log_rows_after, $log_rows_before);
			
		$this->LogableUser->save(array('id' => 301, 'name' => 'Steven Segal', 'counter' => 77));
		
		$result = $this->Log->find('first', array(
			'order' => 'Log.id DESC',
			'conditions' => array('model' => 'LogableUser', 'model_id' => 301)));
		$this->assertEqual($result['Log']['change'], 'name');
	}

}