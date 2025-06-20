<?php

namespace App\Admin\Controllers\Player;

use App\Admin\Controllers\AdminController;
use Xn\Admin\Form;
use Xn\Admin\Grid;
use Xn\Admin\Show;
use App\Models\Player\GameRecord;
use App\Admin\Controllers\Traits\Common;
use App\Helpers\GlobalParam;
use App\Models\GameManage\GameVendor;
use App\Models\Merchant\Game;
use Xn\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
use Xn\FilterDateRangePicker\TimestampRange;

/**
 * 遊戲紀錄
 */
class GameRecordController extends AdminController
{
    use Common;

    /**
     * 遊戲名稱
     *
     * @var array
     */
    static $gameName;

    /**
     * 遊戲房名
     *
     * @var array
     */
    static $arenaName;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '遊戲紀錄';


    /**
     * 遊戲詳情
     *
     */
    static function gameResult(string $vendor, string $game, string $id)
    {
        switch(strtoupper($vendor)) {
            case 'XGD': // 新高登棋牌
            case 'GDX': // 高登電子
            case 'GPS':
                $vendor_ = strtoupper($vendor)==='XGD'?'xgd':'gdx';
                $obj = DB::table("mc_vendors_view")->where(['merchant_code' => session('merchant_code'), 'vendor_code' => 'XGD'])->first('params');
                $params = json_decode($obj->params??"{}", true);
                $host = $params['result_host']??'';
                $token = $params['access_token']??'';
                return "{$host}/{$vendor_}_game_result?token={$token}&bet_id={$id}";
                break;
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        GameRecordController::$gameName = GlobalParam::GameName(session('merchant_code', ''), session('lang', ''));
        $grid = new Grid(new GameRecord);
        $grid->column('game_code', __('遊戲代碼'))->display(function($v){
            return GameRecordController::$gameName[$v]??$v;
        });
        $grid->column('bill_time', __('結算時間'))->hide();
        $grid->column('start_time', __('開局時間'));
        $grid->column('round_number', __('局號'))->hide();
        $grid->column('parent_bet_id', __('父單號'));
        $grid->column('bet_id', __('單號'));
        $grid->column('account', __('帳號'))->totalRow(__('合計'));
        $grid->column('bet_amount', __('下注額'));
        $grid->column('effective_bet_amount', __('有效投注'))->totalRow();
        $grid->column('winlose_amount', __('輸贏金額'))->totalRow();
        $grid->column('before_balance', __('下注前餘額'));
        $grid->column('transfer_amount', __('交易金額'))->totalRow();
        $grid->column('balance', __('餘額'));
        $grid->column('fee', __('費用'))->totalRow();
        if (env('APP_DEBUG')) {
            $grid->column('card_value_display', __('原牌值'))->display(function($v){
                return $this->card_value;
            })->hide();
        }
        $grid->column('win_point', __('獲得點數'))->hide();
        $grid->column('bet_point', __('下注點數'))->hide();
        // $grid->column('pg_id', 'pg_id')->hide();
        $grid->model()->where('merchant_code', session('merchant_code'))->orderBy('start_time', 'desc');
        // filter
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->column(1/3, function ($filter) {
                $filter->equal('account', __('帳號'));

                $filter->use((new TimestampRange('start_time', __('開局時間')))->timezone(session('timezone')))->daterangepicker('default', ['autoUpdateInput'=>false]);
                $filter->group('win_point', __('獲得點數'), function ($group) {
                    $group->where('>', function($query){
                        $query->where("win_point", ">", floatval($this->input));
                    });
                });
            });
            $filter->column(1/3, function ($filter) {
                $lang = session('locale');
                $filter->equal('game_code', __('遊戲'))
                ->select(Game::filterGameName(session('merchant_code'), session('locale'))->pluck('game_name', 'game_code'));

                // $filter->equal('parent_bet_id', __('form.parent_bet_id'));
                $filter->use((new TimestampRange('bill_time', __('結算時間')))->timezone(session('timezone')))->daterangepicker('default', ['autoUpdateInput'=>false]);
                $filter->group('winlose_amount', __('輸贏金額'), function ($group) {
                    $group->where('>=', function($query){
                        $query->where("winlose_amount", ">=", floatval($this->input));
                    });
                    $group->where('<=', function($query){
                        $query->where("winlose_amount", "<=", floatval($this->input));
                    });
                });
            });
            $filter->column(1/3, function ($filter) {
                $filter->where(function ($query) {
                    $query->where('bet_id', '=', $this->input)
                        ->orWhere('parent_bet_id', '=', $this->input);
                }, __('單號'), 'mix_bet_id');
                $filter->equal('round_number', __('局號'));
            });
        });
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            //
            $betId = $actions->row["bet_id"];
            $url = GameRecordController::gameResult(
                $actions->row["vendor_code"],
                $actions->row["game_code"],
                $betId,
            );
            $options = [
                "caption" => __('遊戲詳情'),
                "src" => $url,
                "type" => "iframe",
                "iframe" => [
                    "css" => [
                        "height" => '90%',
                        "width" => '90%',
                        "max-width" => '1280px'
                    ]
                ]
            ];
            $actions->appendHtml("<a data-fancybox data-options='" . json_encode($options) ."'  href='javascript:;'>【".__('遊戲詳情')."】</a>");
        });
        $grid->disableCreateButton();
        // 预设不显示资料
        if (empty($_GET)||(count($_GET) ==1 && isset($_GET['_pjax']))) {
            $grid->model()->where("parent_bet_id", "null");
        }

        if (empty($_GET['mix_bet_id']) && empty($_GET['round_number'])) {
            if (empty($_GET['start_time']) && empty($_GET['bill_time'])) {
                $grid->model()->where("parent_bet_id", "null");
                admin_toastr(
                    __('validation.required', ['attribute' => __('開局時間')." / ". __('結算時間')]), 'error', [
                    "positionClass" => "toast-top-center",
                    "preventDuplicates" => 1
                ]);
            }
        }

        $grid->fixColumns(1, -1);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(GameRecord::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $form = new Form(new GameRecord);
        $form->hidden('id');
        return $form;
    }
}

