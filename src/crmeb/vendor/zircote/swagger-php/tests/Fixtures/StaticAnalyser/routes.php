<?php declare(strict_types=1);

//
// Allow indentation with tab(s).
//
// http://www.doctrine-project.org/jira/browse/DCOM-255
// https://github.com/zircote/swagger-php/issues/168
// https://github.com/zircote/swagger-php/issues/203
//
// @codingStandardsIgnoreStart
//

   /**
    *
    *	@OA\Put(
    * 		path="/users/{id}",
    * 		tags={"users"},
    * 		operationId="updateUser",
    * 		summary="Update user entry",
    * 		@OA\Parameter(
    * 			name="id",
    * 			in="path",
    * 			required=true,
    * 			description="UUID",
    * 		),
    * 		@OA\Parameter(
    * 			name="user",
    * 			in="cookie",
    * 			required=true,
    * 			@OA\Schema(ref="#/components/schemas/User"),
    *		),
    * 		@OA\Response(
    * 			response=200,
    * 			description="success",
    * 		),
    * 		@OA\Response(
    * 			response="default",
    * 			description="error",
    * 			@OA\Schema(ref="#/components/schemas/Error"),
    * 		),
    * 	)
    * @OA\Options(
    * path="/users/{id}",
    * @OA\Response(response=200,description="Some CORS stuff"),
    * @OA\Response(response="4XX",description="Some Client Error response"),
    * @OA\Response(response="5XX",description="Some Server Error response")
    * )
    */
   Route::put('/users/{user_id}', 'UserController@update');

    /**
     *
     * 	@OA\Delete(
     * 		path="/users/{id}",
     * 		tags={"users"},
     * 		operationId="deleteUser",
     * 		summary="Remove user entry",
     * 		@OA\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			description="UUID",
     * 		),
     * 		@OA\Response(
     * 			response=200,
     * 			description="success",
     * 		),
     * 		@OA\Response(
     * 			response="default",
     * 			description="error",
     * 			@OA\Schema(ref="#/components/schemas/Error"),
     * 		),
     * 	)
     *
     */
    Route::delete('/users/{user_id}', 'UserController@destroy');

      /**
      *@OA\Head(path="/users/{id}",@OA\Response(response=200,description="Only checking if it exists"))
      */
     Route::get('/users/{user_id}', 'UserController@show');

/**
 * @OA\Schema(schema="Error")
 * @OA\Schema(schema="User")
 */
//
// @codingStandardsIgnoreEnd
//
