<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Exceedone\Exment\Model\ApiClient;
use Exceedone\Exment\Model\ApiClientRepository;
use Exceedone\Exment\Enums\ApiClientType;
use Laravel\Passport\Client;

class ApiSettingController extends AdminControllerBase
{
    use HasResourceActions;

    public function __construct(Request $request)
    {
        $this->setPageInfo(exmtrans("api.header"), exmtrans("api.header"), exmtrans("api.description"), 'fa-code-fork');
    }
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ApiClient);
        $grid->column('client_type_text', exmtrans('api.client_type_text'));
        $grid->column('name', exmtrans('api.app_name'));
        $grid->column('id', exmtrans('api.client_id'));
        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('user_id', exmtrans('common.created_user'))->display(function ($user_id) {
            return getUserName($user_id, true);
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
        $form = new Form(new ApiClient);
        $client = ApiClient::find($id);
        
        $form->description(exmtrans('common.help.more_help'));

        if (!isset($id)) {
            $form->radio('client_type', exmtrans('api.client_type_text'))->options(ApiClientType::transArray('api.client_type_options'))
            ->default(ApiClientType::CLIENT_CREDENTIALS)
            ->required()
            ->attribute(['data-filtertrigger' =>true])
            ->help(exmtrans('common.help.init_flg'));
        } else {
            $form->display('client_type_text', exmtrans('api.client_type_text'));
            $form->hidden('client_type');
        }
        
        $form->text('name', exmtrans('api.app_name'))->required();

        ///// toggle showing redirect
        // if create or password
        if (!isset($client) || $client->client_type == ApiClientType::CLIENT_CREDENTIALS) {
            $form->url('redirect', exmtrans('api.redirect'))
            ->required()
            ->help(exmtrans('api.help.redirect'))
            ->attribute(['data-filter' => json_encode(['key' => 'client_type', 'value' => [ApiClientType::CLIENT_CREDENTIALS]])]);
        }

        if (isset($id)) {
            $form->text('id', exmtrans('api.client_id'))->setElementClass(['copyScript'])->readonly();
            $form->password('secret', exmtrans('api.client_secret'))->readonly()->toggleShowEvent()
                ->setElementClass(['copyScript'])
                ->help(exmtrans('api.help.client_secret'));

            if ($client->client_type == ApiClientType::API_KEY) {
                $client_api_key = $client->client_api_key;

                $form->password('client_api_key.key', exmtrans('api.api_key'))->readonly()->toggleShowEvent()
                    ->setElementClass(['copyScript'])
                    ->help(exmtrans('api.help.api_key') . exmtrans('api.help.client_secret'));

                $form->display('user_id', exmtrans('common.executed_user'))->displayText(function ($user_id) {
                    return getUserName($user_id, true);
                })->help(exmtrans('api.help.executed_user'));
            }
        }

        $form->disableReset();
        return $form;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return mixed
     */
    public function store()
    {
        return $this->saveData();
    }

    /**
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return $this->saveData($id);
    }

    /**
     * create or update data
     *
     * @param string $id
     * @return Response
     */
    protected function saveData($id = null)
    {
        $request = request();

        // validation
        $validates = [
            'name' => 'required',
            'redirect' => 'url',
        ];
        if (!isset($id)) {
            $validates['client_type'] = 'required';
        }

        $validator = \Validator::make($request->all(), $validates);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $clientRepository = new ApiClientRepository;
        DB::beginTransaction();
        try {
            // for create token
            if (!isset($id)) {
                $user_id = \Exment::user()->getUserId();
                $name = $request->get('name');
                $client_type = $request->get('client_type');

                // create for CLIENT_CREDENTIALS
                if ($client_type == ApiClientType::CLIENT_CREDENTIALS) {
                    $client = $clientRepository->create(
                        $user_id,
                        $name,
                        $request->get('redirect')
                    );
                }
                // create for password
                elseif ($client_type == ApiClientType::PASSWORD_GRANT) {
                    $client = $clientRepository->createPasswordGrantClient(
                        $user_id,
                        $name,
                        'http://localhost'
                    );
                } elseif ($client_type == ApiClientType::API_KEY) {
                    $client = $clientRepository->createApiKey(
                        $user_id,
                        $name,
                        'http://localhost'
                    );
                }
            }
            // update info
            else {
                $client = ApiClient::find($id);
                $client->name = $request->get('name');
                if ($client->client_type == ApiClientType::CLIENT_CREDENTIALS) {
                    $client->redirect = $request->get('redirect');
                }
                $client->save();
            }
            DB::commit();

            admin_toastr(trans('admin.update_succeeded'));
            $url = admin_urls('api_setting', $client->id, 'edit');
            return redirect($url);
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
