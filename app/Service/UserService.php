<?php

namespace SamuelAndrey\Belajar\PHP\MVC\Service;

use Exception;
use SamuelAndrey\Belajar\PHP\MVC\Config\Database;
use SamuelAndrey\Belajar\PHP\MVC\Domain\User;
use SamuelAndrey\Belajar\PHP\MVC\Exception\ValidationException;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserLoginRequest;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserLoginResponse;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserRegisterRequest;
use SamuelAndrey\Belajar\PHP\MVC\Model\UserRegisterResponse;
use SamuelAndrey\Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        $this->validateUserRegistrationRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->findById($request->id);
            if ($user != null) {
                throw new ValidationException("User Id already exist");
            }
    
            $user = new User();
            $user->id = $request->id;
            $user->name = $request->name;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);
    
            $this->userRepository->save($user);
    
            $response = new UserRegisterResponse();
            $response->user = $user;

            Database::commitTrasaction();
            return $response;
        } catch (Exception $exception) {
            Database::rollbackTrasaction();
            throw $exception;
        }   
    }

    private function validateUserRegistrationRequest(UserRegisterRequest $request)
    {
        if ($request->id == null || $request->name == null || $request->password == null ||
        trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == null) {

            throw new ValidationException("Id, Name, Password can not blank");
        }

    }

    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->validateUserLoginRequest($request);

        $user = $this->userRepository->findById($request->id);
        if ($user == null) {
            throw new ValidationException("Id or password is wrong");
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserLoginResponse();
            $response->user = $user;
            return $response;
        } else {
            throw new ValidationException("Id or password is wrong");
        }
    }

    private function validateUserLoginRequest(UserLoginRequest $request)
    {
        if ($request->id == null  || $request->password == null ||
        trim($request->id) == "" || trim($request->password) == null) {

            throw new ValidationException("Id, Password can not blank");
        }
    }

    public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
    {
        $this->validateProfileUpdateRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException("User is not found");
            }

            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTrasaction();

            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return $response;

        } catch (Exception $exception) {
            Database::rollbackTrasaction();
            throw $exception;
        }
    }

    private function validateProfileUpdateRequest(UserProfileUpdateRequest $request)
    {
        if ($request->id == null  || $request->name == null ||
        trim($request->id) == "" || trim($request->name) == null) {

            throw new ValidationException("Id, Name can not blank");
        }
    }

    public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
    {
        $this->validateUserPasswordUpdateRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException("User is not found");
            }

            if (!password_verify($request->oldPassword, $user->password)) {
                throw new ValidationException("Old password is wrong");
            }

            $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
            $this->userRepository->update($user);

            Database::commitTrasaction();

            $response = new UserPasswordUpdateResponse();
            $response->user = $user;
            return $response;

        } catch (Exception $exception) {
            Database::rollbackTrasaction();
            throw $exception;
        }
    }

    private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request)
    {
        if ($request->id == null || $request->oldPassword == null || $request->newPassword == null ||
        trim($request->id) == "" || trim($request->oldPassword) == "" || trim($request->newPassword) == null) {

            throw new ValidationException("Id, Old Password, New Password can not blank");
        }
    }
}