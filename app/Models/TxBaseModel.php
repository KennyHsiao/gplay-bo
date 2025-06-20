<?php
/**
 * https://stackoverflow.com/questions/26757452/laravel-eloquent-accessing-properties-and-dynamic-table-names
 */
namespace App\Models;

use App\Helpers\GlobalParam;
use App\Models\System\Database;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Cache;

class TxBaseModel extends Eloquent
{
    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $txdb = strtolower(session()->get('agent_code')."_tx");
        $txdb = $txdb === '_tx' ? "pgsql_tmp" : $txdb;
        $cacheKey = "database_".session('agent_code');
        $db = Cache::rememberForever($cacheKey, function () {
            return Database::where([
                'agent_code' => session('agent_code'),
                'slug' => 'db'
            ])->select('tx_db', 'rep_db')->first();
        });
        GlobalParam::CreateTxDbConfig($db->tx_db, strtolower(session('agent_code')."_tx"));
        $this->setConnection($txdb);
        parent::__construct($attributes);
    }
}

?>
