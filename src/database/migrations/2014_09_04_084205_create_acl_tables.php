<?php

use Cupparis\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAclTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {


		if (!Schema::hasTable('acl_permissions')) {
			Schema::create('acl_permissions', function(Blueprint $table) {
				$table->string('id')->primary();
				$table->text('route');
				$table->boolean('resource_id_required');
				$table->string('name');
			});
		}

		if (!Schema::hasTable('acl_users_permissions')) {
			Schema::create('acl_users_permissions', function(Blueprint $table) {
				$table->increments('id');
				$table->string('permission_id')->index();
				$table->integer('user_id')->index();
                $table->boolean('allowed')->default(false);
				$table->text('ids')->nullable();
			});
		}

		if (!Schema::hasTable('acl_roles_permissions')) {
			Schema::create('acl_roles_permissions', function(Blueprint $table) {
				$table->increments('id');
				$table->string('permission_id')->index();
				$table->string('role_id')->index();
                $table->boolean('allowed')->default(false);
				$table->text('ids')->nullable();
			});
		}

		if (!Schema::hasTable('acl_roles')) {
			Schema::create('acl_roles', function(Blueprint $table) {
				$table->string('id')->primary();
				$table->string('name');
			});
		}

		if (!Schema::hasTable('acl_users_roles')) {
			Schema::create('acl_users_roles', function(Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id');
				$table->string('role_id');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('acl_users_permissions');
		Schema::drop('acl_roles_permissions');
		Schema::drop('acl_users_roles');
		Schema::drop('acl_roles');
		Schema::drop('acl_permissions');
	}

}
