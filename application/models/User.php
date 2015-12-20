<?php
class God_Model_User extends God_Model_Base_User
{
    public function changePassword($password)
    {

    }

    public function getFullName()
    {
        if ($this->firstname && $this->surname) {

            return sprintf('%s %s', $this->firstname, $this->surname);
        }

        return false;
    }

    public function hasRole($roleName)
    {

    }
}