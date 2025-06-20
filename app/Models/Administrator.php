<?php

namespace App\Models;

use App\Models\Platform\Company;
use Xn\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Xn\Admin\Auth\Database\HasPermissions;
use Xn\Admin\Facades\Admin;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['username', 'password', 'name', 'avatar', 'auth_method', 'google2fa_secret'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'admin_user_companies', 'admin_user_id', 'company_code', 'id', 'code');
    }

    public function agent() {
        return $this->belongsTo(Company::class, 'company_code', 'code');
    }

    public function merchants() {
        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('finance') || Admin::user()->isRole('cs')) {
            return Company::select(DB::raw("CONCAT('(',code,') ',name) AS name"), "code", "id", "parent_id")
                ->with(['merchants'=>function($q){
                    $q->select(DB::raw("CONCAT('(',code,') ',name) AS name"), "code", "id", "parent_id")
                    ->whereHas('merchant', function($q){
                        $q->where('status', '1');
                    })->orderBy('order');
                }])->where('type', 'agent')->orderBy('order')->get()->toArray();
        } else if (Admin::user()->isRole('merchant')) {
            $codes = Admin::user()->companies()->pluck('code');
            return Company::select(DB::raw("CONCAT('(',code,') ',name) AS name"), "code")->whereIn('code', $codes)->get()->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
        } else {
            // 代理、業務、客服
            $codes = Admin::user()->companies()->pluck('code');
            $query =  Company::select(DB::raw("CONCAT('(',code,') ',name) AS name"), "code", "id", "parent_id")
            ->with(['merchants'=>function($q)use($codes){
                $q->select(DB::raw("CONCAT('(',code,') ',name) AS name"), "code", "id", "parent_id")
                ->whereHas('merchant', function($q){
                    $q->where('status', '1');
                });

                if (count($codes)>0){
                    $q = $q->whereIn('code', $codes);
                }
            }])->where('type', 'agent');
            return $query->orderBy('order')->get()->toArray();
        }
    }
}
