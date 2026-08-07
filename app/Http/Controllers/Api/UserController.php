<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->users->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return UserResource::collection($paginator);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->users->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user = $this->users->update($user, $request->validated());

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user): Response
    {
        $this->users->delete($user, $request->user());

        return response()->noContent();
    }
}
