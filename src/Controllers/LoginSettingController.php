<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Encore\Admin\Widgets\Form as WidgetForm;
use Exceedone\Exment\Model\LoginSetting;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Model\ApiClientRepository;
use Exceedone\Exment\Model\RoleGroup;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Enums\LoginType;
use Exceedone\Exment\Enums\LoginProviderType;
use Exceedone\Exment\Services\Installer\InitializeFormTrait;
use Laravel\Passport\Client;
use Encore\Admin\Layout\Content;

class LoginSettingController extends AdminControllerBase
{
    use InitializeFormTrait;
    use HasResourceActions;

    public function __construct(Request $request)
    {
        $this->setPageInfo(exmtrans("login.header"), exmtrans("login.header"), exmtrans("login.description"), 'fa-sign-in');
    }
    
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request, Content $content)
    {
        $content = $this->AdminContent($content);

        $content->row($this->grid());
        $content->row($this->globalSettingBox($request));
        return $content;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LoginSetting);
        $grid->column('login_type', exmtrans('login.login_type'))->displayEscape(function($v){
            return LoginType::getEnum($v)->transKey('login.login_type_options');
        });
        $grid->column('name', exmtrans('login.login_setting_name'));
        $grid->column('active_flg', exmtrans("plugin.active_flg"))->displayEscape(function ($active_flg) {
            return boolval($active_flg) ? exmtrans("common.available_true") : exmtrans("common.available_false");
        });

        $grid->disableFilter();
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new LoginSetting);
        $login_setting = LoginSetting::find($id);

        $errors = $this->checkOauthLibraries();

        $form->description(exmtrans('common.help.more_help'));

        $form->text('name', exmtrans('login.login_setting_name'))->required();

        if (!isset($id)) {
            $form->radio('login_type', exmtrans('login.login_type'))->options(LoginType::transArrayFilter('login.login_type_options', LoginType::SSO()))
            ->required()
            ->attribute(['data-filtertrigger' =>true])
            ->help(exmtrans('common.help.init_flg'));
        } else {
            $form->display('login_type_text', exmtrans('login.login_type'));
            $form->hidden('login_type');
        }
        
        $form->switchbool('active_flg', exmtrans("plugin.active_flg"))->default(false);

        $form->embeds('options', exmtrans("login.options"), function (Form\EmbeddedForm $form) use ($login_setting, $errors) {
            ///// toggle 
            // if create or oauth
            if (!isset($login_setting) || $login_setting->login_type == LoginType::OAUTH) {
                $this->setOAuthForm($form, $login_setting, $errors);
            }
            if (!isset($login_setting) || $login_setting->login_type == LoginType::SAML) {
                $this->setSamlForm($form, $login_setting, $errors);
            }
            

            $form->exmheader(exmtrans('login.user_setting'))->hr();

            $form->select('mapping_user_column', exmtrans("login.mapping_user_column"))
            ->required()
            ->config('allowClear', false)
            ->help(exmtrans('login.help.mapping_user_column'))
            ->options(['user_code' => exmtrans("user.user_code"), 'email' => exmtrans("user.email")])
            ->default('email');

            $form->switchbool('sso_jit', exmtrans("login.sso_jit"))
            ->help(exmtrans("login.help.sso_jit"))
            ->default(false)
            ->attribute(['data-filtertrigger' =>true]);

            $form->multipleSelect('jit_rolegroups', exmtrans("role_group.header"))
            ->help(exmtrans('login.help.jit_rolegroups'))
            ->options(function ($option) {
                return RoleGroup::all()->pluck('role_group_view_name', 'id');
            })
            ->attribute(['data-filter' => json_encode(['key' => 'options_sso_jit', 'value' => '1'])]);
            
            $form->switchbool('update_user_info', exmtrans("login.update_user_info"))
            ->help(exmtrans("login.help.update_user_info"))
            ->default(true);

            if (!isset($login_setting) || $login_setting->login_type == LoginType::SAML) {
                $form->description(exmtrans("login.help.mapping_description"))
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

                $form->text('mapping_user_code', exmtrans("user.user_code"))
                ->required()
                ->default('user_code')
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

                $form->text('mapping_user_name', exmtrans("user.user_name"))
                ->required()
                ->default('user_name')
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

                $form->text('mapping_email', exmtrans("user.email"))
                ->required()
                ->default('email')
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
            }


            $form->exmheader(exmtrans('login.login_button'))->hr();
            
            $form->text('login_button_label', exmtrans('login.login_button_label'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_label'));
            
            $form->icon('login_button_icon', exmtrans('login.login_button_icon'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_icon'));

            $form->color('login_button_background_color', exmtrans('login.login_button_background_color'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_background_color'));

            $form->color('login_button_background_color_hover', exmtrans('login.login_button_background_color_hover'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_background_color_hover'));

            $form->color('login_button_font_color', exmtrans('login.login_button_font_color'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_font_color'));

            $form->color('login_button_font_color_hover', exmtrans('login.login_button_font_color_hover'))
            ->default(null)
            ->help(exmtrans('login.help.login_button_font_color_hover'));
            
        })->disableHeader();

        $form->disableReset();
        return $form;
    }

    protected function setOAuthForm($form, $login_setting, $errors){
        if(array_has($errors, LoginType::OAUTH)){
            $form->description($errors[LoginType::OAUTH])
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);

            return;
        }

        $form->select('oauth_provider_type', exmtrans('login.oauth_provider_type'))
        ->options(LoginProviderType::transKeyArray('login.oauth_provider_type_options'))
        ->required()
        ->attribute(['data-filtertrigger' => true, 'data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);

        $login_provider_caution = '<span class="red">' . exmtrans('login.message.oauth_provider_caution', [
            'url' => getManualUrl('sso'),
        ]) . '</span>';
        $form->description($login_provider_caution)
        ->attribute(['data-filter' => json_encode(['key' => 'options_provider_type', 'value' => [LoginProviderType::OTHER]])]);

        $form->text('oauth_provider_name', exmtrans('login.oauth_provider_name'))
        ->required()
        ->help(exmtrans('login.help.login_provider_name'))
        ->attribute(['data-filter' => json_encode(['key' => 'options_oauth_provider_type', 'value' => [LoginProviderType::OTHER]])]);

        $form->text('oauth_client_id', exmtrans('login.oauth_client_id'))
        ->required()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);

        $form->text('oauth_client_secret', exmtrans('login.oauth_client_secret'))
        ->required()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);

        $form->text('oauth_scope', exmtrans('login.oauth_scope'))
        ->help(exmtrans('login.help.scope'))
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);

        if (boolval(config('exment.expart_mode', false))) {
            $form->url('oauth_redirect_url', exmtrans('login.redirect_url'))
            ->help(exmtrans('login.help.redirect_url'))
            ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::OAUTH]])]);
        }
    }

    protected function setSamlForm($form, $login_setting, $errors){
        if(array_has($errors, LoginType::SAML)){
            $form->description($errors[LoginType::SAML])
                ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

            return;
        }
        
        if(!isset($login_setting)){
            $form->text('saml_name', exmtrans('login.saml_name'))
            ->help(sprintf(exmtrans('common.help.max_length'), 30) . exmtrans('common.help_code'))
            ->required()
            ->rules(["max:30", "regex:/".Define::RULES_REGEX_SYSTEM_NAME."/", new \Exceedone\Exment\Validator\SamlNameUniqueRule])
            ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);    
        }
        else{
            $form->display('saml_name_text', exmtrans('login.saml_name'))->default(function() use($login_setting){
                return $login_setting->getOption('saml_name');
            });
            $form->hidden('saml_name');
        }

        $form->exmheader(exmtrans('login.saml_idp'))->hr()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

        $form->text('saml_idp_entityid', exmtrans('login.saml_idp_entityid'))
        ->help(exmtrans('login.help.saml_idp_entityid'))
        ->required()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->url('saml_idp_sso_url', exmtrans('login.saml_idp_sso_url'))
        ->help(exmtrans('login.help.saml_idp_sso_url'))
        ->required()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->url('saml_idp_ssout_url', exmtrans('login.saml_idp_ssout_url'))
        ->help(exmtrans('login.help.saml_idp_ssout_url'))
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->textarea('saml_idp_x509', exmtrans('login.saml_idp_x509'))
        ->help(exmtrans('login.help.saml_idp_x509'))
        ->rows(4)
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        

        $form->exmheader(exmtrans('login.saml_sp'))->hr()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

        $form->multipleSelect('saml_sp_name_id_format', exmtrans('login.saml_sp_name_id_format'))
        ->help(exmtrans('login.help.saml_sp_name_id_format'))
        ->required()
        ->options(Define::SAML_NAME_ID_FORMATS)
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->text('saml_sp_entityid', exmtrans('login.saml_sp_entityid'))
        ->help(exmtrans('login.help.saml_sp_entityid'))
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->textarea('saml_sp_x509', exmtrans('login.saml_sp_x509'))
        ->help(exmtrans('login.help.saml_sp_x509'))
        ->rows(4)
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->textarea('saml_sp_privatekey', exmtrans('login.saml_sp_privatekey'))
        ->help(exmtrans('login.help.saml_privatekey'))
        ->rows(4)
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        

        $form->exmheader(exmtrans('login.saml_option'))->hr()
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->switchbool('saml_option_name_id_encrypted', exmtrans("login.saml_option_name_id_encrypted"))
        ->help(exmtrans("custom_column.help.saml_option_name_id_encrypted"))
        ->default("0")
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);
        
        $form->switchbool('saml_option_authn_request_signed', exmtrans("login.saml_option_authn_request_signed"))
        ->help(exmtrans("custom_column.help.saml_option_authn_request_signed"))
        ->default("0")
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

        $form->switchbool('saml_option_logout_request_signed', exmtrans("login.saml_option_logout_request_signed"))
        ->help(exmtrans("custom_column.help.saml_option_logout_request_signed"))
        ->default("0")
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);

        $form->switchbool('saml_option_logout_response_signed', exmtrans("login.saml_option_logout_response_signed"))
        ->help(exmtrans("custom_column.help.saml_option_logout_response_signed"))
        ->default("0")
        ->attribute(['data-filter' => json_encode(['key' => 'login_type', 'parent' => 1, 'value' => [LoginType::SAML]])]);


    }

    /**
     * Checking OAuth library
     *
     * @return void
     */
    protected function checkOauthLibraries(){
        $errors = [];
        if(!class_exists('\\Laravel\\Socialite\\SocialiteServiceProvider')){
            $errors[] = LoginType::OAUTH();
        }

        if(!class_exists('\\Aacotroneo\\Saml2\\Saml2Auth')){
            $errors[] = LoginType::SAML();
        }

        return collect($errors)->mapWithKeys(function($error){
            return [$error->getValue() => '<span class="red">' . exmtrans('login.message.not_install_library', [
                'name' => $error->transKey('login.login_type_options'),
                'url' => getManualUrl('sso'),
            ]) . '</span>'];
        });
    }
    
    /**
     * Send data for global setting
     * @param Request $request
     */
    protected function globalSettingBox(Request $request)
    {
        $form = new WidgetForm(System::get_system_values(['login']));
        $form->disableReset();
        $form->action(admin_url('login_setting/postglobal'));


        $form->exmheader(exmtrans('system.password_policy'))->hr();
        $form->description(exmtrans("system.help.password_policy"));

        $form->switchbool('complex_password', exmtrans("system.complex_password"))
            ->help(exmtrans("system.help.complex_password"));

        $form->number('password_expiration_days', exmtrans("system.password_expiration_days"))
            ->default(0)
            ->min(0)
            ->max(999)
            ->help(exmtrans("system.help.password_expiration_days"));

        $form->number('password_history_cnt', exmtrans("system.password_history_cnt"))
            ->default(0)
            ->min(0)
            ->max(20)
            ->help(exmtrans("system.help.password_history_cnt"));
    
        if (!is_nullorempty(config('exment.login_providers'))) {
            $form->exmheader(exmtrans('login.sso_setting'))->hr();

            $form->switchbool('show_default_login_provider', exmtrans("login.show_default_login_provider"))
                ->help(exmtrans("login.help.show_default_login_provider"))
                ->attribute(['data-filtertrigger' => true]);

            $form->switchbool('sso_redirect_force', exmtrans("login.sso_redirect_force"))
                ->help(exmtrans("login.help.sso_redirect_force"))
                ->attribute(['data-filter' => json_encode(['key' => 'show_default_login_provider', 'value' => '0'])]);

            $form->textarea('sso_accept_mail_domain', exmtrans('login.sso_accept_mail_domain'))
                ->help(exmtrans("login.help.sso_accept_mail_domain"))
                ->rows(3)
                ->attribute(['data-filter' => json_encode(['key' => 'sso_jit', 'value' => '1'])])
                ;
        }

        $box = new Box(exmtrans('common.detail_setting'), $form);
        return $box;
    }
    
    /**
     * Send data for global setting
     * @param Request $request
     */
    public function postGlobal(Request $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->postInitializeForm($request, ['login'], false, false);
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return $result;
            }

            DB::commit();

            admin_toastr(trans('admin.save_succeeded'));

            return redirect(admin_url('login_setting'));
        } catch (Exception $exception) {
            //TODO:error handling
            DB::rollback();
            throw $exception;
        }
    }
}