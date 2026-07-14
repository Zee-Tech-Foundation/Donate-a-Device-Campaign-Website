<?php
if (!defined('SITE_NAME')) {
    exit('Captcha template could not be loaded.');
}

function render_captcha_field(): string
{
    $question = generate_captcha_question();
    ob_start();
    ?>
      <div class="field">
        <label for="captcha">Security check</label>
        <div class="captcha-question"><?= esc($question); ?></div>
        <input id="captcha" name="captcha" type="text" required autocomplete="off" />
      </div>
    <?php
    return ob_get_clean();
}
