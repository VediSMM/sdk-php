<?php

declare(strict_types=1);

namespace VediSMM;

use VediSMM\Service\AccountsService;
use VediSMM\Service\AnalyticsService;
use VediSMM\Service\AuditService;
use VediSMM\Service\AuthService;
use VediSMM\Service\CalendarService;
use VediSMM\Service\ConnectionsService;
use VediSMM\Service\GroupsService;
use VediSMM\Service\JobsService;
use VediSMM\Service\MediaService;
use VediSMM\Service\NetworksService;
use VediSMM\Service\PersonalTokensService;
use VediSMM\Service\PostsService;
use VediSMM\Service\PreferencesService;
use VediSMM\Service\ProfileService;
use VediSMM\Service\SessionsService;
use VediSMM\Service\SystemService;
use VediSMM\Service\WebhooksService;

final class VediSMM
{
    /** @var array<string, list<string>> */
    private const OPERATION_OWNERSHIP = [
        'system' => ['getOpenApi', 'ping'],
        'auth' => ['forgotPassword', 'login', 'logout', 'logoutAll', 'refresh', 'register', 'resendVerification', 'resetPassword', 'verifyEmail'],
        'profile' => ['changePassword', 'deleteMe', 'getMe', 'updateMe'],
        'sessions' => ['getSession', 'listSessions', 'revokeSession'],
        'audit' => ['listAuditEvents'],
        'personalTokens' => ['createPersonalToken', 'getPersonalToken', 'listPersonalTokens', 'revokePersonalToken', 'rotatePersonalToken', 'updatePersonalToken'],
        'preferences' => ['createContentTemplate', 'deleteContentTemplate', 'getContentTemplate', 'getSignatures', 'listContentTemplates', 'replaceSignatures', 'updateContentTemplate'],
        'networks' => ['getNetwork', 'listNetworks'],
        'connections' => ['cancelAccountConnection', 'confirmAccountConnection', 'getAccountConnection', 'startAccountConnection'],
        'accounts' => ['disconnectAccount', 'getAccount', 'listAccounts', 'verifyAccount'],
        'groups' => ['createGroup', 'deleteGroup', 'getGroup', 'listGroups', 'replaceGroupAccounts', 'updateGroup'],
        'media' => ['deleteMedia', 'getMedia', 'getMediaContent', 'getSignedMediaContent', 'listMedia', 'uploadMedia'],
        'posts' => ['checkPostConstraints', 'createPostDraft', 'deletePostDraft', 'getPost', 'listPosts', 'schedulePost', 'unschedulePost', 'updatePostDraft'],
        'jobs' => ['deletePostEverywhere', 'getPublicationJob', 'listPublicationJobs', 'publishPost', 'retryPostTargets'],
        'calendar' => ['listCalendarEvents'],
        'analytics' => ['getAnalyticsAudience', 'getAnalyticsNetworks', 'getAnalyticsSummary', 'getAnalyticsTimeseries', 'listAnalyticsPosts'],
        'webhooks' => ['createWebhook', 'deleteWebhook', 'getWebhook', 'getWebhookDelivery', 'listWebhookDeliveries', 'listWebhooks', 'retryWebhookDelivery', 'rotateWebhookSecret', 'testWebhook', 'updateWebhook'],
    ];

    public readonly Client $client;

    public readonly SystemService $system;

    public readonly AuthService $auth;

    public readonly ProfileService $profile;

    public readonly SessionsService $sessions;

    public readonly AuditService $audit;

    public readonly PersonalTokensService $personalTokens;

    public readonly PreferencesService $preferences;

    public readonly NetworksService $networks;

    public readonly ConnectionsService $connections;

    public readonly AccountsService $accounts;

    public readonly GroupsService $groups;

    public readonly MediaService $media;

    public readonly PostsService $posts;

    public readonly JobsService $jobs;

    public readonly CalendarService $calendar;

    public readonly AnalyticsService $analytics;

    public readonly WebhooksService $webhooks;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();
        $this->system = new SystemService($this->client);
        $this->auth = new AuthService($this->client);
        $this->profile = new ProfileService($this->client);
        $this->sessions = new SessionsService($this->client);
        $this->audit = new AuditService($this->client);
        $this->personalTokens = new PersonalTokensService($this->client);
        $this->preferences = new PreferencesService($this->client);
        $this->networks = new NetworksService($this->client);
        $this->connections = new ConnectionsService($this->client);
        $this->accounts = new AccountsService($this->client);
        $this->groups = new GroupsService($this->client);
        $this->media = new MediaService($this->client);
        $this->posts = new PostsService($this->client);
        $this->jobs = new JobsService($this->client);
        $this->calendar = new CalendarService($this->client);
        $this->analytics = new AnalyticsService($this->client);
        $this->webhooks = new WebhooksService($this->client);
    }

    /** @return array<string, list<string>> */
    public static function operationOwnership(): array
    {
        return self::OPERATION_OWNERSHIP;
    }

    public function __toString(): string
    {
        return (string) $this->client;
    }
}
