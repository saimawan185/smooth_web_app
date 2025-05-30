<?php

namespace Modules\ChattingManagement\Http\Controllers\Api\New;

use App\Broadcasting\DriverRideChatChannel;
use App\Broadcasting\RideChatChannel;
use App\Events\CustomerRideChatEvent;
use App\Events\DriverRideChatEvent;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\AdminModule\Entities\AdminNotification;
use Modules\AdminModule\Service\Interface\AdminNotificationServiceInterface;
use Modules\BusinessManagement\Service\Interface\QuestionAnswerServiceInterface;
use Modules\ChattingManagement\Service\Interface\ChannelConversationServiceInterface;
use Modules\ChattingManagement\Service\Interface\ChannelListServiceInterface;
use Modules\ChattingManagement\Service\Interface\ChannelUserServiceInterface;
use Modules\ChattingManagement\Transformers\ChannelConversationResource;
use Modules\ChattingManagement\Transformers\ChannelListResource;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\UserManagement\Service\UserService;

class ChattingController extends Controller
{
    protected $channelConversationService;
    protected $channelListService;
    protected $channelUserService;
    protected $tripRequestService;
    protected $userService;

    protected $adminNotificationService;
    protected $questionAnswerService;

    public function __construct(ChannelConversationServiceInterface $channelConversationService, ChannelListServiceInterface $channelListService,
                                ChannelUserServiceInterface         $channelUserService, TripRequestServiceInterface $tripRequestService, UserService $userService,
                                AdminNotificationServiceInterface   $adminNotificationService, QuestionAnswerServiceInterface $questionAnswerService
    )
    {
        $this->channelConversationService = $channelConversationService;
        $this->channelListService = $channelListService;
        $this->channelUserService = $channelUserService;
        $this->tripRequestService = $tripRequestService;
        $this->userService = $userService;
        $this->adminNotificationService = $adminNotificationService;
        $this->questionAnswerService = $questionAnswerService;
    }

    public function findChannel(Request $request)
    {
        $channel = $this->channelListService->findOne($request['channel_id']);
        $trip = $this->tripRequestService->findOne($channel->channelable->id);
        return response()->json(responseFormatter(DEFAULT_200, $trip?->current_status), 200);
    }


    public function channelList(Request $request): JsonResponse
    {
        $relation = [
            'channel_users.user', 'channel_conversations', 'last_channel_conversations.conversation_files',
        ];
        $whereHasRelation = [
            'channel_conversations' => [],
            'last_channel_conversations' => [],
            'channel_users' => [['user_id', '=', $request->user()->id]]
        ];
        $chatList = $this->channelListService->getBy(whereHasRelations: $whereHasRelation, relations: $relation, orderBy: ['updated_at' => 'DESC'], limit: $request['limit'], offset: $request['offset']);
        $chatList = ChannelListResource::collection($chatList);
        return response()->json(responseFormatter(DEFAULT_200, $chatList, $request['limit'], $request['offset']));
    }

    public function createChannel(Request $request): JsonResponse
    {
        $channelIds = $this->channelUserService->getBy(criteria: ['user_id' => $request->user()->id]);
        $channelIds = $channelIds->pluck('channel_id')->toArray();

        $whereInCriteria = [
            'channel_id' => $channelIds
        ];
        $criteria = [
            'user_id' => $request['to']
        ];
        $user = $this->userService->findOne(id: $request['to']);
        $channelUser = $this->channelUserService->findOneBy(criteria: $criteria, whereInCriteria: $whereInCriteria);
        if ($channelUser) {
            $findChannel = $this->channelListService->findOne($channelUser?->channel_id);
            if ($findChannel) {
                $request->merge(['channelable_id' => $request['trip_id']]);
                $findChannel = $this->channelListService->update(id: $findChannel?->id, data: $request->all());
                return response()->json(responseFormatter(DEFAULT_200, ['user' => $user, 'channel' => ChannelListResource::make($findChannel)]), 200);
            }
        }
        $channel = $this->channelListService->createChannelWithChannelUser($request->all());
        return response()->json(responseFormatter(DEFAULT_STORE_200, ['user' => $user, 'channel' => ChannelListResource::make($channel)]), 200);
    }


    public function sendMessage(Request $request): JsonResponse
    {
        $column = 'customer_id';
        if ($request->user()->user_type == 'driver') {
            $column = 'driver_id';
        }
        $attributes[$column] = auth()->user()->id;
        $relations = ['customer', 'driver'];
        $whereInCriteria = [
            'current_status' => [ONGOING, ACCEPTED]
        ];
        $trip = $this->tripRequestService->findOneBy(criteria: $attributes, whereInCriteria: $whereInCriteria, relations: $relations);
        if (!$trip) {
            return response()->json(responseFormatter(constant: TRIP_REQUEST_404), 403);
        }
        $user = auth()->user();
        DB::beginTransaction();
        $this->channelListService->update(id: $request['channel_id'], data: ['updated_at' => now()]);
        $channelUserData = [
            'is_read' => false,
        ];

        $this->channelUserService->updatedBy(criteria: ['channel_id' => $request['channel_id'], 'user_id' => $user->id], data: $channelUserData);
        $attributes = [
            'channel_id' => $request['channel_id'],
            'message' => $request['message'],
            'user_id' => $user->id,
            'trip_id' => $request['trip_id'],
            'is_read' => 0,
        ];
        if ($request->has('files')) {
            $attributes['files'] = $request->file('files');
        }
        $channelConversation = $this->channelConversationService->create($attributes);

        $channelConversationWithFiles = $this->channelConversationService->findOne(id: $channelConversation?->id, relations: ['user', 'conversation_files', 'channel']);
        if ($user->user_type == 'driver') {
            $to_user = $trip->customer;
            $user_id = $to_user->id;
            try {
                checkPusherConnection(CustomerRideChatEvent::broadcast($trip, $channelConversationWithFiles));
            } catch (Exception $exception) {

            }
        } else {
            $to_user = $trip->driver;
            $user_id = $to_user->id;
            try {
                checkPusherConnection(DriverRideChatEvent::broadcast($trip, $channelConversationWithFiles));
            } catch (Exception $exception) {

            }
        }
        $this->channelConversationService->updatedBy(criteria: ['user_id' => $user_id, 'channel_id' => $request['channel_id']], data: ['is_read' => 1]);
        DB::commit();

        $push = getNotification('new_message');

        sendDeviceNotification(
            fcm_token: $to_user->fcm_token,
            title: translate($push['title']),
            description: translate(textVariableDataFormat(value: $push['description'], userName: $user?->full_name ?? $user?->first_name)),
            status: $push['status'],
            ride_request_id: $trip->id,
            type: $request->channel_id,
            action: 'new_message_arrived',
            user_id: $user_id, user_name: $user?->first_name . " " . $user?->last_name
        );
        return response()->json(responseFormatter(DEFAULT_STORE_200), 200);
    }


    public function conversation(Request $request): JsonResponse
    {

        $user = auth()->user();
        $channelUser = $this->channelUserService->findOneBy(criteria: ['channel_id' => $request['channel_id'], ['user_id', '!=', $user->id]]);
        $channelUserData = [
            'is_read' => true,
        ];
        $this->channelUserService->updatedBy(criteria: ['channel_id' => $request['channel_id'], 'user_id' => $user->id], data: $channelUserData);
        $attributes = [
            'channel_id' => $request['channel_id'],
        ];
        $whereHasRelations = [
            'channel.channel_users' => ['user_id' => $user->id]
        ];
        $this->channelConversationService->updatedBy(criteria: ['channel_id' => $request['channel_id'], 'user_id' => $channelUser->user_id], data: ['is_read' => 1]);
        $conversations = $this->channelConversationService->getBy(
            criteria: $attributes,
            whereHasRelations: $whereHasRelations,
            relations: ['user', 'conversation_files'],
            orderBy: ['id' => 'desc'],
            limit: $request['limit'],
            offset: $request['offset']
        );
        $conversations = ChannelConversationResource::collection($conversations);
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $conversations, limit: $request['limit'], offset: $request['offset']));
    }

    public function createOrFindChannel(Request $request): JsonResponse
    {
        $channelIds = $this->channelUserService->getBy(criteria: ['user_id' => $request->user()->id]);

        return response()->json();
    }

    public function createChannelWithAdmin(Request $request): JsonResponse
    {
        $channelIds = $this->channelUserService->getBy(criteria: ['user_id' => $request->user()->id]);
        $channelIds = $channelIds->pluck('channel_id')->toArray();

        $whereInCriteria = [
            'channel_id' => $channelIds
        ];
        $superAdmin = $this->userService->findOneBy(criteria: [
            'user_type' => 'super-admin'
        ]);
        $criteria = [
            'user_id' => $superAdmin->id
        ];

        $channelUser = $this->channelUserService->findOneBy(criteria: $criteria, whereInCriteria: $whereInCriteria);

        if ($channelUser) {
            $findChannel = $this->channelListService->findOne($channelUser?->channel_id);
            if ($findChannel) {
                $findChannel = $this->channelListService->update(id: $findChannel?->id, data: $request->all());
                return response()->json(responseFormatter(DEFAULT_200, ['user' => $superAdmin, 'channel' => ChannelListResource::make($findChannel)]), 200);
            }
        }
        $channel = $this->channelListService->createChannelWithAdmin(data: ['to' => $superAdmin->id]);
        return response()->json(responseFormatter(DEFAULT_STORE_200, ['user' => $superAdmin, 'channel' => ChannelListResource::make($channel)]), 200);
    }

    public function sendMessageToAdminFromDriver(Request $request): JsonResponse
    {
        $user = auth()->user();
        $channel = $this->channelListService->findOneBy(criteria: ['id' => $request['channel_id']]);

        if (!$channel) {
            return response()->json(responseFormatter(constant: CHANNEL_NOT_FOUND_404), 403);
        }

        $channelUser = $this->channelUserService->findOneBy(criteria: ['channel_id' => $request['channel_id'], 'user_id' => $user->id]);

        if (!$channelUser) {
            return response()->json(responseFormatter(constant: CHANNEL_NOT_FOUND_404), 403);
        }

        DB::beginTransaction();

        $channelUserData = [
            'is_read' => true,
        ];
        $this->channelUserService->updatedBy(criteria: ['channel_id' => $request['channel_id'], ['user_id', '=', $user->id]], data: $channelUserData);

        $attributes = [
            'channel_id' => $request['channel_id'],
            'message' => $request['message'],
            'user_id' => $user->id,
            'is_read' => 0,
        ];
        if ($request->has('files')) {
            $attributes['files'] = $request->file('files');
        }
        $channelConversation = $this->channelConversationService->create($attributes);
        $adminNotificationData = [
            'model' => 'channel_conversation',
            'model_id' => $channelConversation->id,
            'message' => 'new_message_arrived'
        ];
        $this->adminNotificationService->create($adminNotificationData);
        DB::commit();

        return response()->json(responseFormatter(DEFAULT_STORE_200), 200);
    }

    public function sendPredefinedQuestionToAdminFromDriver(Request $request): JsonResponse
    {
        $user = auth()->user();
        $channel = $this->channelListService->findOneBy(criteria: ['id' => $request['channel_id']]);

        if (!$channel) {
            return response()->json(responseFormatter(constant: CHANNEL_NOT_FOUND_404), 403);
        }

        $channelUser = $this->channelUserService->findOneBy(criteria: ['channel_id' => $request['channel_id'], 'user_id' => $user->id]);

        if (!$channelUser) {
            return response()->json(responseFormatter(constant: CHANNEL_NOT_FOUND_404), 403);
        }
        $channelUserAnother = $this->channelUserService->findOneBy(criteria: ['channel_id' => $request['channel_id'], ['user_id', '!=', $user->id]]);

        $channelUserData = [
            'is_read' => true,
        ];
        $this->channelUserService->updatedBy(criteria: ['channel_id' => $request['channel_id'], ['user_id', '=', $user->id]], data: $channelUserData);
        $questionAnswer = $this->questionAnswerService->findOneBy(criteria: ['id' => $request->question_id]);

        $attributes = [
            'channel_id' => $request['channel_id'],
            'message' => $questionAnswer?->question,
            'user_id' => $user->id,
            'is_read' => 1,
        ];
        $channelConversation = $this->channelConversationService->create($attributes);
        if ($channelConversation) {
            $attributes1 = [
                'channel_id' => $request['channel_id'],
                'message' => $questionAnswer?->answer,
                'user_id' => $channelUserAnother?->user_id,
                'is_read' => 1,
            ];
            $this->channelConversationService->create($attributes1);
        }
        return response()->json(responseFormatter(DEFAULT_STORE_200), 200);
    }

}
