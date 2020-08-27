<?php


namespace Phoenix\Page;


/**
 * Class LoginPage
 *
 * @author James Jones
 * @package Phoenix\Page
 *
 */
class LoginPage extends Page
{
    /**
     * @return string
     */
    public function renderBody(): string
    {
        ob_start();
        ?>
        <div class="container">
            <div class="row text-center">
                <div class="col">
                    <div class="grey-bg p-3">
                        <?php
                        //Messages::instance()->getMessagesHTML();
                        ?>
                        <form method='post' class='form' id="login-form">
                            <div class="form-group">
                                <label for="pin-field">Your Pin</label>
                                <input id="pin-field" name='pin' type='text' class='form-control text-center' data-validation='number'
                                       maxlength="4"
                                       autofocus/>
                            </div>
                            <div class="form-group">
                                <label for="password-field">Your Password</label>
                                <input id="password-field" name='password' type='password' class='form-control text-center' autofocus/>
                            </div>
                            <input id="login-button" type='submit' value='Login' class='btn btn-success btn-lg btn-block mt-4'>
                            <input name="login-attempt" type='hidden' value='submit'>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function renderHeader(): string
    {
        ob_start();
        ?>
        <div class="row text-center">
            <div class="col-md-12 logo_title py-3">
                <img src="img/logo.png"/>
                <h1 class="crm-title mb-0 text-center" style="text-align: center"><?php echo SYSTEM_TITLE; ?></h1>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}