<?php
namespace Sludio\HelperBundle\Steam;

interface SteamLoginInterface
{
    public function url($return);
	public function validate();
}
