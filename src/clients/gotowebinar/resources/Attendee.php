<?php

namespace verbb\auth\clients\gotowebinar\resources;

use verbb\auth\clients\gotowebinar\resultset\SimpleResultSet;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Attendee extends AuthenticatedResourceAbstract
{
    /**
     * Get session attendees
     *
     * @throws IdentityProviderException
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAttendees
     */
    public function getSessionAttendees(string $webinarKey, string $sessionKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/attendees', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get attendee
     *
     * @throws IdentityProviderException
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAttendee
     */
    public function getAttendee(string $webinarKey, string $sessionKey, string $registrantKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/attendees/{registrantKey}', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey,
            'registrantKey' => $registrantKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get attendee poll answers
     *
     * @throws IdentityProviderException
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAttendeePollAnswers
     */
    public function getAttendeePollAnswers(string $webinarKey, string $sessionKey, string $registrantKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/attendees/{registrantKey}/polls', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey,
            'registrantKey' => $registrantKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get attendee questions
     *
     * @throws IdentityProviderException
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAttendeeQuestions
     */
    public function getAttendeeQuestions(string $webinarKey, string $sessionKey, string $registrantKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/attendees/{registrantKey}/questions', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey,
            'registrantKey' => $registrantKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }

    /**
     * Get attendee survey answers
     *
     * @throws IdentityProviderException
     *
     * @link https://developer.goto.com/GoToWebinarV2/#operation/getAttendeeSurveyAnswers
     */
    public function getAttendeeSurveyAnswers(string $webinarKey, string $sessionKey, string $registrantKey): SimpleResultSet
    {
        $url = $this->getRequestUrl('/organizers/{organizerKey}/webinars/{webinarKey}/sessions/{sessionKey}/attendees/{registrantKey}/surveys', [
            'webinarKey' => $webinarKey,
            'sessionKey' => $sessionKey,
            'registrantKey' => $registrantKey
        ]);
        $request = $this->provider->getAuthenticatedRequest('GET', $url, $this->accessToken);
        return new SimpleResultSet($this->provider->getParsedResponse($request));
    }
}
