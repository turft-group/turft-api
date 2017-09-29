<?php

namespace App\Http\Controllers\API;

use App\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    protected $response;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::orderBy('created_at', 'desc')->get();

        return $groups;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->validateRequest($request)) {
            return $this->response;
        }

        $new = new Group;
        $new->name = $request->name;
        $new->save();

        return response()->json([
            'created' => true
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  Group $group
     * @return Group
     */
    public function show(Group $group)
    {
        if (!$this->checkGroupRight($group, 'member')) {
            return $this->response;
        }
        return $group->load('users');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        if (!$this->checkGroupRight($group, 'owner')) {
            return $this->response;
        }

        if (!$this->validateRequest($request)) {
            return $this->response;
        }

        $group->update($request->all());

        return response()->json([
            'updated' => true
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        if (!$this->checkGroupRight($group, 'owner')) {
            return $this->response;
        }
        if ($group->delete()) {
            return response()->json([
                'deleted' => true
            ], Response::HTTP_OK);
        } else {
            return $this->response = response()->json([
                'data' => [
                    'message' => 'Deleting was unsuccessful',
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    protected function validateRequest(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50|unique:groups',
        ]);

        if ($validator->fails()) {
            $this->response = response()->json([
                'data' => [
                    'message' => 'Validation was not successful',
                    'errors' => json_encode($validator->errors()),
                    'status_code' => Response::HTTP_BAD_REQUEST
                ]
            ], Response::HTTP_BAD_REQUEST);
            return false;
        }

        return true;
    }

    /**
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    protected function addUser(Group $group) {
        $group->load("users");
        $type = Input::get('type');
        $userId = Input::get('user');
        $check = $type == "owner" || $type == "admin" ? "owner" : "admin";
        $role = $group->getUserRole($userId);
        if ($role == "admin" || $role == "owner" || $type == "admin" || $type == "owner") {
            $check = "owner";
        }
        if (!$this->checkGroupRight($group, $check)) {
            return $this->response;
        }
        if ($role) {
            $group->updateUser($userId, $type);
        } else {
            $group->addUser($userId, $type);
        }
        return response()->json([
            'added' => true
        ], Response::HTTP_OK);
    }

    /**
     * @param Group $group
     * @param string $type
     * @return bool
     */
    protected function checkGroupRight($group, $type)
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return true;
        }
        switch ($type) {
            case "member":
                $authorized = $group->hasUser($user);
                break;
            case "admin":
                $authorized = $group->isAdmin($user);
                break;
            case "owner":
                $authorized = $group->isOwner($user);
                break;
            default:
                $authorized = false;
        }

        if ($authorized) {
            return true;
        } else {
            $this->response = response()->json(["Response" => "Forbidden"], Response::HTTP_FORBIDDEN);
            return false;
        }
    }
}
