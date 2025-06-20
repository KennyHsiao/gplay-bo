<?php
/**
 * https://stackoverflow.com/questions/26757452/laravel-eloquent-accessing-properties-and-dynamic-table-names
 */
namespace App\Models;

use App\Helpers\GlobalParam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class RepBaseModel extends Eloquent
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;
    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!Config::has('database.connections.agent_db')) {

            $agentDb =  DB::table('sys_databases')->first(['rep_db', 'mongo_index']);

            GlobalParam::CreateRepDbConfig($agentDb->setting, 'agent_db');
        }
        $this->setConnection("agent_db");
        parent::__construct($attributes);
    }
}

?>
