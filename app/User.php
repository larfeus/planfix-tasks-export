<?php

namespace App;

use App\Contracts\Entity;

class User extends Entity
{
	/**
	 * @var string
	 */
	public $username;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * @var string
	 */
	public $sid;
}