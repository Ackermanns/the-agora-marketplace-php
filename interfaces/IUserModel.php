<?php
interface IUserModel {
    // The minimum properties the user Model must have

    function setUser($username, $password);

    function getUserDetails($username);

    function addContact($adduser);

}