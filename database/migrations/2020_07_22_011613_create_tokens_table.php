<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->increments('tokens_id');
            $table->integer('tokens_guard_id')->unsigned()->comment('类型关联主键')->nullable();
            $table->string('tokens_guard_type')->comment('类型')->nullable();
            $table->string('tokens_type')->default('DEFAULT')->comment('认证渠道')->nullable();
            $table->string('tokens_token')->comment('访问令牌')->nullable();
            $table->dateTime('tokens_create_time')->comment('创建时间')->nullable();
            $table->dateTime('tokens_update_time')->comment('更新时间')->nullable();
            $table->dateTime('tokens_expires_time')->comment('过期时间')->nullable();
            $table->string('tokens_revoked')->default('DISABLE')->comment('状态{NORMAL:正常}{DISABLE:禁用}')->nullable();
            $table->index('tokens_guard_id');
            $table->index('tokens_guard_type');
            $table->index('tokens_type');
            $table->index('tokens_token');
            $table->index('tokens_revoked');
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
        DB::statement("ALTER TABLE " . DB::getTablePrefix() . "tokens comment '认证token'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
