<?php
$I = new WebGuy($scenario);
$I->am('visitor');
$I->wantTo('check highlight text');
$I->amOnPage('/login?goto=news');
$I->seeElement(['xpath' => "//input[@type='submit']"]);
$I->seeElement('//form//input[@type="text"]');
$I->seeElement("//a[contains(text(), 'Forgot')]");
$I->dontSeeElement(['name' => "Foo bar"]);
$I->dontSeeElement(['link' => "Click here"]);