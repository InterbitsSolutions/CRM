<?php

namespace App\Http\Controllers;

use App\Models\UserRoles;
use App\Models\Modules;
use App\Models\ModuleRelationships;
use Illuminate\Support\Facades\Redirect;

class UserRoleController extends Controller {

    public function __construct() {
        $this->middleware('admin');
    }
    /*Add Role*/
    public function addRole() {
        $module_arr = array();
        $condition_array = array('User');
        $modules = Modules::whereNotIn('module_name', $condition_array)->get()->all();
        foreach ($modules as $module) {
            $module_arr[$module->id] = $module->module_name;
        }
        if (!empty($_POST)) {
            $checkRoleExist = UserRoles::where('role_name', "=", $_POST['role_name'])->first();
            try {
                if (empty($checkRoleExist)) {
                    $addRole = new UserRoles();
                    $addRole->role_name = $_POST['role_name'];
                    $addRole->save();
                    $insertedId = $addRole->id;
                    foreach ($_POST['modules'] as $module) {
                        $assignModule = new ModuleRelationships();
                        $assignModule->role_id = $insertedId;
                        $assignModule->module_id = $module;
                        $assignModule->save();
                    }
                    $message = " Role Added successfully!";
                    flash($message);
                    return Redirect::to("list-user-roles");
                } else {
                    $message = "Role with " . $_POST['role_name'] . " already exist in system, please create with another name!";
                    flash($message);
                    return Redirect::to("list-user-roles");
                }
            } catch (\Aws\S3\Exception\S3Exception $e) {
                $message = $e->getMessage();
                $errorMessage = 'There is some error while adding Users. Please try again later!';
                flash($errorMessage, "danger");
                return Redirect::to('list-user-roles');
            }
        }
        return view('adminsOnly.Roles.addRole', compact('module_arr'));
    }

    public function listRoles() {
        $userRoles = UserRoles::get();
        return view('adminsOnly.Roles.rolelist', compact('userRoles'));
    }

    public function deleteRole($roleID) {
        if (!empty($roleID)) {
            $whereArray = array('id' => $roleID);
            UserRoles::where($whereArray)->delete();
            flash('Role deleted successfully!');
            return Redirect::to("list-user-roles");
        }
    }

    public function editRole($id = null) {
        $activeConfigId = $this->getActiveConfig();
        $editmodule = array();
        $condition_array = array('User');
        $modules = Modules::whereNotIn('module_name', $condition_array)->get()->all();
        foreach ($modules as $module) {
            $module_arr[$module->id] = $module->module_name;
        }
        $condition = ['role_id' => $id];
        $editDetails = ModuleRelationships::where($condition)->get();
        foreach ($editDetails as $edit_module) {
            array_push($editmodule, $edit_module->module_id);
        }
        if (!empty($_POST)) {
            if ($_POST['role_name'] != $_POST['hidden_role_name']) {
                $checkRoleExist = UserRoles::where('role_name', "=", $_POST['role_name'])->first();
                try {
                    if (empty($checkRoleExist)) {
                        $role_name = $_POST['role_name'];
                        $editRoles = UserRoles::find($id);
                        $editRoles->role_name = $role_name;
                        $editRoles->save();
                        $whereArray = array('role_id' => $id);
                        ModuleRelationships::where($whereArray)->delete();

                        foreach ($_POST['modules'] as $module) {
                            $assignModule = new ModuleRelationships();
                            $assignModule->role_id = $id;
                            $assignModule->module_id = $module;
                            $assignModule->save();
                        }
                        $message = " Role Updated successfully!";
                        flash($message);
                        return Redirect::to("list-user-roles");
                    } else {
                        $message = "Role with " . $_POST['role_name'] . " already exist in system, please create with another name!";
                        flash($message, "danger");
                        return Redirect::to("list-user-roles");
                    }
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    $message = $e->getMessage();
                    $errorMessage = 'There is some error while adding Users. Please try again later!';
                    flash($errorMessage, "danger");
                    return Redirect::to('list-user-roles');
                }
            } else {
                $role_name = $_POST['role_name'];
                $editRoles = UserRoles::find($id);
                $editRoles->role_name = $role_name;
                $editRoles->save();
                $whereArray = array('role_id' => $id);
                ModuleRelationships::where($whereArray)->delete();
                foreach ($_POST['modules'] as $module) {
                    $assignModule = new ModuleRelationships();
                    $assignModule->role_id = $id;
                    $assignModule->module_id = $module;
                    $assignModule->save();
                }
                $message = " Role Updated successfully!";
                flash($message);
                return Redirect::to("list-user-roles");
            }
        }
        $userRoles = UserRoles::findOrFail($id);
        return view('adminsOnly.Roles.editRole', compact('userRoles', 'editmodule', 'module_arr'));
    }

    public function viewRole($id) {
        $editmodule = array();
        $condition_array = array('User');
        $modules = Modules::whereNotIn('module_name', $condition_array)->get()->all();
        foreach ($modules as $module) {
            $module_arr[$module->id] = $module->module_name;
        }
        $condition = ['role_id' => $id];
        $editDetails = ModuleRelationships::where($condition)->get();
        foreach ($editDetails as $edit_module) {
            array_push($editmodule, $edit_module->module_id);
        }
        $viewUserRoles = UserRoles::findOrFail($id);
        return view('adminsOnly.Roles.viewRole', compact('viewUserRoles', 'module_arr', 'editmodule'));
    }

    public function checkDuplicateRole() {
        $role_name = $_REQUEST['role_name'];
        $role_condition = array('role_name' => $role_name);
        $role_count = UserRoles::where($role_condition)->get()->count();
        echo $role_count;
        exit;
    }

}
