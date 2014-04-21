<?php

class CreateUserTest extends TestCase
{
	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	 
	 public function testUserCreateRoute()
	 {
	 	$crawler = $this->client->request('GET', '/');

		$this->assertTrue($this->client->getResponse()->isOk());
	 }
	 
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
	 
	 //THE FOLLOWING TESTS SHOULD ALL FAIL
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testUsernameTaken()
	 {
	 	$post_data = array(
	 	'username' => 'abc123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertEquals($this->client->getResponse()->isRedirect('/'), FALSE);
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testEmailTaken()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'abc123@nus.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testUsernameValidity()
	 {
	 	$post_data = array(
	 	'username' => 'z z z ...///',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordValidity()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'pass',
	 	'retype-password' => 'pass'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordsMatch()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'pass'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testEmailValidity()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'hotmail',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testUsernameEmpty()
	 {
	 	$post_data = array(
	 	'username' => '',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testDisplayNameEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => '',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testEmailEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im Sleeping',
	 	'email' => '',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testPasswordEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im Sleeping',
	 	'email' => 'new@hotmail.com',
	 	'password' => '',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 /**
	  * @depends testInitialize
	  */
	 public function testRetypePasswordEmpty()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im Sleeping',
	 	'email' => 'new@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => ''
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$this->assertFalse($this->client->getResponse()->isRedirect('/'));
	 }
	 
	 //This test should be successful!
	 /**
	  * @depends testInitialize
	  */
	 public function testCreateSecondUser()
	 {
	 	$post_data = array(
	 	'username' => 'zzz123',
	 	'display-name' => 'Im SLEEPING',
	 	'email' => 'zzz@hotmail.com',
	 	'password' => 'password',
	 	'retype-password' => 'password'
		);
	 	$crawler = $this->client->request('POST', '/user', $post_data);
		$user = User::where('username','zzz123')->get();
		$this->assertEquals($user.email, 'zzz@hotmail.com');
	 }	 
}
