<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use App\Models\AppointmentModel;
use App\Services\AppointmentSchedulingService;
use App\Libraries\ApiRuntimeContext;

class Appointment extends ResourceController
{
    protected $modelName = AppointmentModel::class;
    protected $format    = 'json';
    protected AppointmentSchedulingService $schedulingService;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->schedulingService = new AppointmentSchedulingService();
    }

    public function index()
    {
        $clinicId = ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $fields = [
            'id',
            'patient_id',
            'appointment_id',
            'appointment_date',
            'appointment_time',
            'duration',
            'appointment_type',
            'chair_name',
            'status',
            'notes',
        ];

        $db = Database::connect();
        if ($db->fieldExists('provider_user_id', 'appointments')) {
            $fields[] = 'provider_user_id';
        }

        $data = $this->model->select(implode(', ', $fields))
            ->where('clinic_id', $clinicId)
            ->where('appointment_date >=', date('Y-m-d'))
            ->orderBy('appointment_date', 'ASC')
            ->orderBy('appointment_time', 'ASC')
            ->findAll(100);

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $clinicId = ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        if (\App\Services\ClinicSchedulingSettings::positiveInt($id) === null) {
            return $this->failNotFound('Appointment not found');
        }

        $fields = [
            'id',
            'patient_id',
            'appointment_id',
            'appointment_date',
            'appointment_time',
            'duration',
            'appointment_type',
            'chair_name',
            'status',
            'notes',
            'created_at',
            'updated_at',
        ];

        $db = Database::connect();
        if ($db->fieldExists('provider_user_id', 'appointments')) {
            $fields[] = 'provider_user_id';
        }

        $data = $this->model->select(implode(', ', $fields))
            ->where('clinic_id', $clinicId)
            ->find((int) $id);

        if (!$data) {
            return $this->failNotFound('Appointment not found');
        }

        return $this->respond($data);
    }

    public function create()
    {
        $clinicId = ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $userId = ApiRuntimeContext::getUserId();
        if (!$userId) {
            return $this->failUnauthorized('USER_CONTEXT_REQUIRED');
        }

        $contentType = $this->request->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'application/json')) {
            $rawBody = (string) $this->request->getBody();
            if (trim($rawBody) !== '') {
                $input = json_decode($rawBody, true);
                if (json_last_error() !== JSON_ERROR_NONE || !(json_decode($rawBody) instanceof \stdClass)) {
                    return $this->fail('Invalid JSON payload.', 422, 'INVALID_JSON');
                }
            } else {
                $input = [];
            }
        } else {
            $input = $this->request->getPost();
            if (!is_array($input)) {
                $input = [];
            }
        }

        $result = $this->schedulingService->createForClinic($clinicId, $input, $userId, 'bearer');

        if (!$result['success']) {
            return $this->mapServiceFailure($result);
        }

        return $this->respondCreated($result['appointment'] ?? ['id' => $result['id']]);
    }

    public function update($id = null)
    {
        $clinicId = ApiRuntimeContext::getClinicId();
        if (!$clinicId) {
            return $this->failForbidden('TENANT_CONTEXT_REQUIRED');
        }

        $userId = ApiRuntimeContext::getUserId();
        if (!$userId) {
            return $this->failUnauthorized('USER_CONTEXT_REQUIRED');
        }

        if (\App\Services\ClinicSchedulingSettings::positiveInt($id) === null) {
            return $this->failNotFound('Appointment not found');
        }

        $contentType = $this->request->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'application/json')) {
            $rawBody = (string) $this->request->getBody();
            if (trim($rawBody) !== '') {
                $input = json_decode($rawBody, true);
                if (json_last_error() !== JSON_ERROR_NONE || !(json_decode($rawBody) instanceof \stdClass)) {
                    return $this->fail('Invalid JSON payload.', 422, 'INVALID_JSON');
                }
            } else {
                $input = [];
            }
        } else {
            $input = $this->request->getRawInput();
            if (!is_array($input)) {
                $input = [];
            }
        }

        $result = $this->schedulingService->updateForClinic($clinicId, (int) $id, $input, $userId, 'bearer');

        if (!$result['success']) {
            return $this->mapServiceFailure($result);
        }

        return $this->respond($result['appointment'] ?? ['id' => $result['id']]);
    }

    public function delete($id = null)
    {
        // Explicitly deny unsupported delete with JSON 405
        return $this->fail('Delete operation is not supported via this API endpoint.', 405);
    }

    protected function mapServiceFailure(array $result)
    {
        $code = $result['code'] ?? 'ERROR';
        $message = $result['message'] ?? 'Appointment scheduling error.';

        switch ($code) {
            case 'FORBIDDEN':
            case 'SUBSCRIPTION_INACTIVE':
                return $this->failForbidden($message);
            case 'PATIENT_NOT_FOUND':
            case 'APPOINTMENT_NOT_FOUND':
                return $this->failNotFound($message);
            case 'SLOT_UNAVAILABLE':
            case 'PROVIDER_NOT_WORKING':
            case 'OUTSIDE_WORKING_HOURS':
            case 'NON_WORKING_DAY':
            case 'PAST_APPOINTMENT_LOCKED':
                return $this->fail($message, 409, $code);
            case 'INVALID_DST_INTERVAL':
            case 'AMBIGUOUS_DST_INTERVAL':
            case 'CROSS_MIDNIGHT':
            case 'INVALID_DATE':
            case 'INVALID_TIME':
            case 'INVALID_DURATION':
            case 'INVALID_PATIENT':
            case 'INVALID_PROVIDER':
            case 'INVALID_PROCEDURE':
            case 'INVALID_APPOINTMENT_TYPE':
            case 'INVALID_NOTES':
            case 'PROVIDER_REQUIRED':
            case 'CHAIR_REQUIRED':
            case 'INVALID_CHAIR':
            case 'PATIENT_IMMUTABLE':
            case 'STATUS_TRANSITION_FORBIDDEN':
                return $this->failValidationErrors([$code => $message]);
            default:
                return $this->fail($message, 400, $code);
        }
    }
}
