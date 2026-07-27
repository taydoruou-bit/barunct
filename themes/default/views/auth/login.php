<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <title><?= $title ?> - Alphasoftware.vn</title>

    <script type="text/javascript">if (parent.frames.length !== 0) {

        top.location = '<?=site_url('pos')?>';

    }</script>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="<?= $assets ?>images/favicon.ico"/>

    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>

    <link href="<?= $assets ?>styles/style.css" rel="stylesheet"/>

    <link href="<?= $assets ?>styles/helpers/login.css" rel="stylesheet"/>

    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>

    <!--[if lt IE 9]>

    <script src="<?= $assets ?>js/respond.min.js"></script>

    <![endif]-->



</head>



<body class="login-page">

    <noscript>

        <div class="global-site-notice noscript">

            <div class="notice-inner">

                <p>

                    <strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in

                    your browser to utilize the functionality of this website.

                </p>

            </div>

        </div>

    </noscript>

    <div class="page-back">

        <div class="text-center">

            <?php if ($Settings->logo2) {

                echo '<img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" class="login-brand-logo" />';

            } 					

			/* tiến hành kiểm tra ACTIVE*/	

			$CI =get_instance();		

			$CI->load->model('auth_model');	

			$result = $CI->auth_model->scodeweb_username();		
			
			$checkflag= $CI->auth_model->readPostUrl(array("url"=>$result->scodeweb_username));		
				

			?>

        </div>



        <div id="login">

            <div class=" container">



                <div class="login-form-div">

                    <div class="login-content">

                        <?php if ($Settings->mmode) { ?>

                            <div class="alert alert-warning">

                                <button data-dismiss="alert" class="close" type="button">×</button>

                                <?= lang('site_is_offline') ?>

                            </div>

                            <?php 

                        } 

                        if ($error) {

                            ?>

                            <div class="alert alert-danger">

                                <button data-dismiss="alert" class="close" type="button">×</button>

                                <ul class="list-group"><?= $error; ?></ul>

                            </div>

                            <?php

                        } 

                        if ($message) {

                            ?>

                            <div class="alert alert-success">

                                <button data-dismiss="alert" class="close" type="button">×</button>

                                <ul class="list-group"><?= $message; ?></ul>

                            </div>

                            <?php

                        }

                        ?>

                        <?php echo form_open("auth/login", 'class="login" data-toggle="validator"'); ?>

                        <div class="div-title col-sm-12">						
						<?php 							
						if($checkflag=="OK"){				
						?>

                            <h3 class="text-primary"><?= lang('login_to_your_account') ?></h3>	
							<?php 							
						}else{		
						
							echo '<h3 class="text-primary">'.$checkflag.'</h3>';
							echo '<h4 class="text-primary"></h4>';
						}					
						?>

                        </div>						
						<?php 
						if($checkflag=="OK"){	
						?>

                        <div class="col-sm-12">

                            <div class="textbox-wrap form-group">

                                <div class="input-group">

                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>

                                    <input type="text" value="" required="required" class="form-control" name="identity"

                                    placeholder="<?= lang('username') ?>"/>

                                </div>

                            </div>

                            <div class="textbox-wrap form-group" style="position: relative;">

                                <div class="input-group">

                                    <span class="input-group-addon"><i class="fa fa-key"></i></span>

                                    <input type="password" value="" required="required" id="shop_password_eye" class="form-control " name="password"

                                    placeholder="<?= lang('pw') ?>"/>

                                    <i class="fa fa-eye icon-showpass shop_password_eye"></i>

                                </div>

                            </div>

                        </div>

                        <?php						
						}

                        if ($Settings->captcha) {

                            ?>

                            <div class="col-sm-12">

                                <div class="textbox-wrap form-group">

                                    <div class="row">

                                        <div class="col-sm-6 div-captcha-left">

                                            <span class="captcha-image"><?php echo $image; ?></span>

                                        </div>

                                        <div class="col-sm-6 div-captcha-right">

                                            <div class="input-group">

                                                <span class="input-group-addon">

                                                    <a href="<?= base_url(); ?>auth/reload_captcha" class="reload-captcha">

                                                        <i class="fa fa-refresh"></i>

                                                    </a>

                                                </span>

                                                <?php echo form_input($captcha); ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php 

                        } /* echo $recaptcha_html; */ 

                        ?>

						<?php 							
						if($checkflag=="OK"){		
						?>

								<div class="form-action col-sm-12">

									<div class="checkbox pull-left">

										<div class="custom-checkbox">

											<?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>

										</div>

										<span class="checkbox-text pull-left"><label for="remember"><?= lang('remember_me') ?></label></span>

									</div>

									<button type="submit" class="btn btn-success pull-right"><?= lang('login') ?> <!--&nbsp; <i class="fa fa-sign-in"></i>--></button>

								</div>

                        <?php						
						}			
						echo form_close(); ?>

                        <div class="clearfix"></div>

                    </div>				

                    <div class="login-form-links link2">

                        <h4 class="text-danger">Hotline: (+84) 835 799997</h4>                        

                        <a href="#forgot_password" class="text-danger forgot_password_link">Quên mật khẩu</a>

                        

                    </div>

                    <?php 

                    if ($Settings->allow_reg&&$checkflag=="OK") { 

                        ?>


                        <?php 

                    } 

                    ?>

                </div>

            </div>

        </div>



        <div id="forgot_password">

            <div class=" container">

                <div class="login-form-div">

                    <div class="login-content">

                        <?php 

                        if ($error) { 

                            ?>

                            <div class="alert alert-danger">

                                <button data-dismiss="alert" class="close" type="button">×</button>

                                <ul class="list-group"><?= $error; ?></ul>

                            </div>

                            <?php 

                        }

                        if ($message) { 

                            ?>

                            <div class="alert alert-success">

                                <button data-dismiss="alert" class="close" type="button">×</button>

                                <ul class="list-group"><?= $message; ?></ul>

                            </div>

                            <?php 

                        } 

                        ?>

                        <div class="div-title col-sm-12">

                            <h3 class="text-primary"><?= lang('forgot_password') ?></h3>

                        </div>

                        <?php echo form_open("auth/forgot_password", 'class="login" data-toggle="validator"'); ?>

                        <div class="col-sm-12">

                            <p style="color:#fff">

                                Nhập email để phục hồi mật khẩu

                            </p>

                            <div class="textbox-wrap form-group">

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-envelope"></i></span>

                                    <input type="email" name="forgot_email" class="form-control "

                                    placeholder="<?= lang('email_address') ?>" required="required"/>

                                </div>

                            </div>

                            <div class="form-action">

                                <a class="btn btn-success pull-left login_link" href="#login">

                                    <i class="fa fa-chevron-left"></i> <?= lang('back') ?>

                                </a>

                                <button type="submit" class="btn btn-primary pull-right">

                                    <?= lang('submit') ?> &nbsp;&nbsp; <i class="fa fa-envelope"></i>

                                </button>

                            </div>

                        </div>

                        <?php echo form_close(); ?>

                        <div class="clearfix"></div>

                    </div>

                </div>

            </div>

        </div>

        <?php 

        if ($Settings->allow_reg) {

            ?>

            <div id="register">

                <div class="container">

                    <div class="registration-form-div reg-content">

                        <?php echo form_open("auth/register", 'class="login" data-toggle="validator"'); ?>

                        <div class="div-title col-sm-12">

                            <h3 class="text-primary"><?= lang('register_account_heading') ?></h3>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('first_name', 'first_name'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-user"></i></span>

                                    <input type="text" name="first_name" class="form-control " placeholder="<?= lang('first_name') ?>" required="required"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('last_name', 'last_name'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-user"></i></span>

                                    <input type="text" name="last_name" class="form-control " placeholder="<?= lang('last_name') ?>" required="required"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('company', 'company'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-building"></i></span>

                                    <input type="text" name="company" class="form-control " placeholder="<?= lang('company') ?>"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('phone', 'phone'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-phone-square"></i></span>

                                    <input type="text" name="phone" class="form-control " placeholder="<?= lang('phone') ?>" required="required"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('username', 'username'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-user"></i></span>

                                    <input type="text" name="username" class="form-control " placeholder="<?= lang('username') ?>" required="required"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?= lang('email', 'email'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-envelope"></i></span>

                                    <input type="email" name="email" class="form-control " placeholder="<?= lang('email_address') ?>" required="required"/>

                                </div>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?php echo lang('password', 'password1'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-key"></i></span>

                                    <?php echo form_password('password', '', 'class="form-control tip" id="password1" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-regexp-message="'.lang('pasword_hint').'"'); ?>

                                </div>

                                <span class="help-block"><?= lang('pasword_hint') ?></span>

                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="form-group">

                                <?php echo lang('confirm_password', 'confirm_password'); ?>

                                <div class="input-group">

                                    <span class="input-group-addon "><i class="fa fa-key"></i></span>

                                    <?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" required="required" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>

                                </div>

                            </div>

                        </div>



                        <div class="col-sm-12">

                            <a href="#login" class="btn btn-success pull-left login_link">

                                <i class="fa fa-chevron-left"></i> <?= lang('back') ?>

                            </a>

                            <button type="submit" class="btn btn-primary pull-right">

                                <?= lang('register_now') ?> <i class="fa fa-user"></i>

                            </button>

                        </div>



                        <?php echo form_close(); ?>

                        <div class="clearfix"></div>

                    </div>

                </div>

            </div>

        <?php

        }

        ?>

    </div>



    <script src="<?= $assets ?>js/jquery.js"></script>

    <script src="<?= $assets ?>js/bootstrap.min.js"></script>

    <script src="<?= $assets ?>js/jquery.cookie.js"></script>

    <script src="<?= $assets ?>js/login.js"></script>

    <script type="text/javascript">

        $(document).ready(function () {

            localStorage.clear();

            var hash = window.location.hash;

            if (hash && hash != '') {

                $("#login").hide();

                $(hash).show();

            }
            $(".shop_password_eye").click(function(){
                if ($(this).attr('class')=='fa fa-eye icon-showpass shop_password_eye') {
                    $("#shop_password_eye").attr("type","text");
                    $(".shop_password_eye").attr("class","fa fa-eye-slash icon-showpass shop_password_eye");
                }else{
                    $(".shop_password_eye").attr("class","fa fa-eye icon-showpass shop_password_eye");
                    $("#shop_password_eye").attr("type","password");
                }
            });

        });

    </script>

	<style>

	.login-content {

		border-radius: 10px;

		background-color: #c60005!important;

	}



	.div-title.col-sm-12 {

		text-align: center;

		text-transform: uppercase;

		background: #c60005;

		padding: 20px 0px;

		margin: 0px;

		border-radius: 10px;

		border-bottom-right-radius: 0px;

		border-bottom-left-radius: 0px;

		margin-bottom: 20px;

	}



	.login-page .login-form-div {

		max-width: 450px;

	}



	button.btn.btn-success.pull-right {

		background: #0066ff;

		border: 1px solid #fff;

	}



	.login-page .checkbox {

		color: #fff;

	}



	input.form-control {

		border: 1px solid #fff;

	}



	.login-page .input-group .input-group-addon {

		border: 1px solid #fff;

	}

	.login-form-links.link2 {

    background: none;

    border: 0px;

    box-shadow: none;

    padding: 0px;

    margin: 0px;

    text-align: center;

}

.login-form-links.link2 {

    background: none;

    border: 0px;

    box-shadow: none;

    padding: 0px;

    margin: 0px;

    text-align: center;

}



.login-form-links.link2 h4 {

    float: left;

    color: #fff;

    width: 50%;

}



.login-form-links.link2 a {

    float: right;

    color: #fff;

    width: 50%;

    margin: 10px 0px;

    border-left: 1px solid #fff;

}



.page-back.bblue {

    background: none!important;

}

body.login-page{
  background-image: url("https://aloit.vn/images/login/webapp/login_bg.jpg");
  background-repeat: no-repeat;
  background-position: right top;
  background-attachment: scroll;

}

a.btn.btn-success.pull-left.login_link {

    background: #fff;

    border: 1px solid #fff;

    color: #db6200;

}



button.btn.btn-primary.pull-right {

    background: #db6200;

    border: 1px solid #fff;

}

.has-error .form-control {

    border-color: #db6200;

}



.has-error .input-group-addon {

    background: #db6200;

    border-color: #db6200!important;

    color: #fff;

}

.login-brand-logo {

    max-width: 100%;
    width: 410px;
    height: auto;
    margin-bottom: 10px;
    border-radius: 2px;

}

.login-form-links.link1 {

    float: left;

    width: 100%;

    background: none;

    color: #fff;

    text-align: center;

    padding: 0px;

}



.login-form-links.link1 a {

    color: #db6200;

    padding: 0px;

    margin: 0px;

}
i.shop_password_eye {
        z-index: 99;
        bottom: 9px;
        position: absolute;
        right: 5px;
        color: #0066ff;
        cursor: pointer;
    }
	</style>

</body>

</html>
