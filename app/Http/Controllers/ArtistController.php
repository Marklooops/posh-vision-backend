<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Resources\UserResource;
use App\Models\Collection;
use App\Models\User;
use App\ShopifyAdminApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArtistController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $artists = User::where('role', 'artist')->paginate($perPage);
        return UserResource::collection($artists)
            ->additional([
                'meta' => [
                    'total' => $artists->total(),
                    'current_page' => $artists->currentPage(),
                    'per_page' => $artists->perPage(),
                    'last_page' => $artists->lastPage(),
                    'total_pages' => $artists->lastPage(),
                ]
            ]);
    }
    public function store(Request $request, CreateNewUser $creater)
    {
        // return ShopifyAdminApi::productVariantCreate("gid://shopify/Product/8071429914815", [
        //     [
        //         'price' => 10,
        //         'optionValues' => [
        //             "name" => "First",
        //         ]
        //     ]
        // ]);
        //return ShopifyAdminApi::showProductVariantList(8071774535871);
        // return ShopifyAdminApi::createProduct([
        //     'title' => "Hello world",
        //     'description' => "this is dummy product",
        //     "images" => ["https://cdn-front.freepik.com/images/ai/image-generator/gallery/65446.webp", "https://cdn-front.freepik.com/images/ai/image-generator/gallery/65446.webp", "https://cdn-front.freepik.com/images/ai/image-generator/gallery/65446.webp"]
        // ], "gid://shopify/Collection/339293110463");
        //return ShopifyAdminApi::deleteCollection('gid://shopify/Collection/339291472063');
        //return ShopifyAdminApi::createCollection($request->all());
        $user = $creater->create($request->all());
        return new JsonResponse([
            "message" => "Artist successfully created",
            "user" => new UserResource($user),
        ], 200);
    }
    public function update(Request $request, User $artist, UpdateUserProfileInformation $updater)
    {
        if($artist->role === 'artist') {
            $updater->update($artist, $request->all());
            return new JsonResponse([
                "message" => "Artist successfully updated",
                "user" => new UserResource($artist),
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function destroy(User $artist)
    {
        if($artist->role === 'artist') {
            $artist->delete();
            return new JsonResponse([
                "message" => "Artist successfully deleted"
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function show(User $artist)
    {
        if($artist->role === 'artist') {
            return new JsonResponse([
                "message" => "Artist successfully retrieved",
                "user" => new UserResource($artist),
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function updatePassword(Request $request, User $artist, UpdateUserPassword $updater)
    {
        if($artist->role === 'artist') {
            $updater->update($artist, $request->all());
            return new JsonResponse([
                "message" => "Artist password successfully updated",
            ], 400);
        }
        return new JsonResponse([], 400);
    }
    public function handleAccess(User $artist)
    {
        if($artist->role === 'artist') {
            $artist->update([
                'blocked' => !$artist->blocked
            ]);
            return new JsonResponse([
                "message" => "Artist access successfully updated"
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function handleStatus(User $artist, Request $request)
    {
        Validator::make($request->all(), [
            'status' => 'required|in:rejected,approved,pending',
        ])->validate();
        if($artist->role === 'artist') {
            $collection = Collection::where('user_id', $artist->id)->first();
            if($collection) {
                $collection->update([
                    "status" => $request->input('status'),
                ]);
                return new JsonResponse([
                    "message" => "Artist profile status successfully updated",
                ], 200);
            }
        }
        return new JsonResponse([], 400);
    }
}
