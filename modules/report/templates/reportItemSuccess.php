<h1>Report item</h1>

<p>Please complete the form below to report this item:</p>

<?php echo $form['reporter']->renderError() ?>
<form action="<?php echo url_for($route_name,$route_params->getRawValue()) ?>" method="post">
  
<?php echo $form->renderGlobalErrors() ?>
  
<dl>

  <dt><?php echo $form['reporter']->renderLabel() ?></dt>
  <dd>
  <?php echo $form['reporter']->render() ?>
  <?php echo $form['reporter']->renderError() ?>
  </dd>

  <dt><?php echo $form['email']->renderLabel() ?></dt>
  <dd>
  <?php echo $form['email']->render() ?>
  <?php echo $form['email']->renderError() ?>
  </dd>

  <dt><?php echo $form['reason']->renderLabel() ?></dt>
  <dd>
  <?php echo $form['reason']->render() ?>
  <?php echo $form['reason']->renderError() ?>
  </dd>

  <dt><?php echo $form['message']->renderLabel() ?></dt>
  <dd>
  <?php echo $form['message']->render() ?>
  <?php echo $form['message']->renderError() ?>
  </dd>

</dl>

<?php if (isset($form['captcha'])) echo $form['captcha'] ?>

<?php echo $form->renderHiddenFields() ?>

<p><input type="submit" value="Report" /></p>

</form>