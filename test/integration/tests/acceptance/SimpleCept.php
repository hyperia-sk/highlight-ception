<?php
$I = new WebGuy($scenario);
$I->am('visitor');
$I->wantTo('check highlight text');
$I->amOnPage("/");
$I->see("comments");
$I->see("show");
$I->seeElement('.hnname');