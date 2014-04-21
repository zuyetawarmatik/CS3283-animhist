<?php

class VisualizationControllerTest extends TestCase
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
	 
	 public function createNewVisualization()
	 {
	 	$crawler = $this->client->request('GET', 'zzz123/visualization/create');
	 }
}