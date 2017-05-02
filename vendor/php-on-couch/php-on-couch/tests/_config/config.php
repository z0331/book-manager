<?php

/*
 * Copyright (C) 2016 Alexis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of config
 *
 * @author Alexis
 */
class config
{

	private static $instance;
	private $users;

	private function __construct()
	{
		$this->users = [];
		$path = __DIR__ . DIRECTORY_SEPARATOR . '_users.php';
		$defaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'defaultUsers.php';
		if (file_exists($path)) {
			$this->users = require $path;
		} else {
			$this->users = require $defaultPath;
		}
	}

	public function getFirstNormalUser()
	{
		foreach ($this->users as $user)
			if (!array_keys($user, 'isAdmin') || $user['isAdmin'] == false)
				return $user;
		return null;
	}

	public function getFirstAdmin()
	{
		foreach ($this->users as $user)
			if (array_keys($user, 'isAdmin') && $user['isAdmin'] == true)
				return $user;
		return null;
	}

	public function getUrl($host, $port, $user)
	{
		if (isset($user) && isset($user['username']) && isset($user['password']))
			return 'http://' . urlencode($user['username']) . ':' . urlencode($user['password']) . '@' . $host . ':' . $port . '/';
		else
			return "http://$host:$port/";
	}

	public function getUsers()
	{
		return $this->users;
	}

	public static function getInstance()
	{
		if (self::$instance == null)
			$instance = new config();
		return $instance;
	}

}
