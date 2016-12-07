<?php
namespace Sludio\HelperBundle\Steam\LoginInterface;

interface SteamLoginInterface
{
    public function url($return);
	public function validate();
}
