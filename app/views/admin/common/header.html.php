<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="/bower_components/metisMenu/dist/metisMenu.min.css">
  <link rel="stylesheet" href="/admin/css/general.css">
  <link rel="stylesheet" href="/admin/css/menu.css">
  <title>Admin Panel</title>
  <script src="/bower_components/jquery/dist/jquery.min.js"></script>
  <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="/bower_components/metisMenu/dist/metisMenu.min.js"></script>
  
</head>
<body>
  <div id="PageWrapper">

  <?php $this->partial('admin/common/navbar') ?>

  <main class="container-fluid">
    <div class="row">
      <?php $this->partial('admin/common/sidebar') ?>

      <div class="col-xs-10" id="MainContent">
