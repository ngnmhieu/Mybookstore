<?php $this->partial('book/header'); ?>

<h1>Create a Book</h1>
<form action="/book" method="post">
  <label for="name">Name: </label>
  <input type="text" name="name" id="name">
  <input type="submit" name="submit" value="Create">
</form>
