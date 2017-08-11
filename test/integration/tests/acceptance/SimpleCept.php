<?php
$I = new WebGuy($scenario);
$I->wantTo('check user highlight text');
$I->amOnPage("/VisualCeption/seeVisualChanges.php");
$I->see("#intro");
