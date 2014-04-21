<?php

class UpdateSettingsTest extends TestCase
{
	 //NOTE DATABASE MUST BE EMPTY BEFORE YOU RUN THESE TESTS
	 public function testInitialize()
	 {
	 	$post_data = array(
		'username' => 'abc123',
		'display-name' => 'Hi',
		'email' => 'abc123@nus.com',
		'password' => 'password',
		'retype-password' => 'password'
		);
		
		$crawler = $this->client->request('POST', '/user', $post_data);
		
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->email, 'abc123@nus.com');
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testNotAuthorized()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'newpass',
		'password-retype' => 'newpass'		
		);
		
		$crawler = $this->client->request('PUT', '/zzzz123', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/'), TRUE);
	 }
	 
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordMismatch()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'newpass',
		'password-retype' => 'mismatch'		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'password', TRUE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordLength()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'four',
		'password-retype' => 'four'		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'password', TRUE);
	 }

     /**
	  * @depends testInitialize
	  */
	 public function testPasswordEmpty()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => '',
		'password-retype' => 'newpass'		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'password', TRUE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordRetypeEmpty()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'newpass',
		'password-retype' => ''		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'password', TRUE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testDisplayNameEmpty()
	 {
	 	$post_data = array(
	 	'display-name' => '',
		'password' => 'newpass',
		'password-retype' => 'newpass'		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'password', TRUE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testChangePassword()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'newpass',
		'password-retype' => 'newpass'		
		);
		
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$user = User::where('username','abc123')->get();
		$this->assertEquals($user->password, 'newpass', TRUE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testNotLoggedIn()
	 {
	 	$post_data = array(
	 	'display-name' => 'Hi',
		'password' => 'newpass',
		'password-retype' => 'newpass'		
		);
		$crawler = $this->client->request('POST', 'user/logout');
		$crawler = $this->client->request('PUT', '/abc123', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/user/login'), TRUE);
	 }
}
