<?php
$I = new WebGuy($scenario);
$I->wantTo('check highlight text');
$I->amOnPage("/");
$I->see("Výkonnostný Marketing");
