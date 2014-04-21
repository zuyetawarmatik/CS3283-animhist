<?php

class LoginTest extends TestCase
{
	//Initializing lone user in the database.
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
		
		$crawler = $this->client->request('POST', 'user/logout');
	 }
	 
	 //THE FOLLOWING TESTS SHOULD ALL FAIL
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testWrongPassword()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'email' => 'abc123@nus.com',
	 	'password' => 'wrong'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testWrongUserName()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'email' => 'abc123@nus.com',
	 	'password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }

	 /**
	  * @depends testInitialize
	  */
	 public function testWrongEmail()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'email' => 'zzz123@nus.com',
	 	'password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'email' => 'abc123@nus.com',
	 	'password' => ''
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testEmailEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'email' => '',
	 	'password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testUsernameEmpty()
	 {
	 	$post_data = array(
	 	'username' => '',
	 	'email' => 'abc123@nus.com',
	 	'password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), FALSE);
	 }
	 
	 //This test should succeed
	 /**
	  * @depends testInitialize
	  */
	 public function testLogin()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'email' => 'abc123@nus.com',
	 	'password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user/login', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/abc123'), TRUE);
	 }
}
