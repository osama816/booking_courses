<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Booking;
use App\Helper\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;

#[OA\Tag(
    name: "Bookings",
    description: "Endpoints for managing course bookings"
)]
class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    #[OA\Get(
        path: "/api/bookings",
        summary: "Get all bookings",
        description: "Retrieve all course bookings (Admin only)",
        tags: ["Bookings"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Bookings retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bookings retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(
                                        property: "user",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "name", type: "string", example: "John Doe"),
                                            new OA\Property(property: "email", type: "string", example: "john@example.com")
                                        ]
                                    ),
                                    new OA\Property(
                                        property: "course",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "title", type: "string", example: "Laravel Basics"),
                                            new OA\Property(property: "level", type: "string", example: "Beginner"),
                                            new OA\Property(property: "available_seats", type: "integer", example: 15)
                                        ]
                                    ),
                                    new OA\Property(property: "booking_date", type: "string", example: "2024-01-15 10:30:00"),
                                    new OA\Property(property: "created_at", type: "string", example: "2024-01-15 10:30:00")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Request failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Request failed")
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        try {
            $bookings = $this->bookingService->getAllBookings();
            return ApiResponse::success(
                BookingResource::collection($bookings),
                'Bookings retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponse::error('Request failed', ['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: "/api/bookings/{id}",
        summary: "Get single booking",
        description: "Retrieve details of a specific booking",
        tags: ["Bookings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Booking ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Booking retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Booking retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(
                                    property: "user",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                                        new OA\Property(property: "email", type: "string", example: "john@example.com")
                                    ]
                                ),
                                new OA\Property(
                                    property: "course",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "title", type: "string", example: "Laravel Basics"),
                                        new OA\Property(property: "description", type: "string", example: "Learn Laravel from scratch"),
                                        new OA\Property(property: "level", type: "string", example: "Beginner")
                                    ]
                                ),
                                new OA\Property(property: "booking_date", type: "string", example: "2024-01-15 10:30:00")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Booking not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Booking not found")
                    ]
                )
            )
        ]
    )]
    public function show($id)
    {
        try {
            $booking = $this->bookingService->getBooking($id);
            if (!$booking) {
                return ApiResponse::error('Booking not found', [], 404);
            }
            return ApiResponse::success(
                new BookingResource($booking),
                'Booking retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponse::error('Request failed', ['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Post(
        path: "/api/bookings",
        summary: "Create a new booking",
        description: "Book a course for the current authenticated user",
        tags: ["Bookings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["course_id"],
                properties: [
                    new OA\Property(
                        property: "course_id",
                        type: "integer",
                        example: 1,
                        description: "The ID of the course to book"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Course booked successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Course booked successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(
                                    property: "user",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1)
                                    ]
                                ),
                                new OA\Property(
                                    property: "course",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "title", type: "string", example: "Laravel Basics")
                                    ]
                                ),
                                new OA\Property(property: "booking_date", type: "string", example: "2024-01-15 10:30:00")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Booking failed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "No available seats for this course"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Course not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Course not found")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated")
                    ]
                )
            )
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        try {
            $booking = $this->bookingService->createBooking($request->course_id);
            return ApiResponse::success(
                new BookingResource($booking),
                'Course booked successfully',
                201
            );
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }

    #[OA\Put(
        path: "/api/bookings/{id}",
        summary: "Update booking (Not used)",
        description: "Update method is not implemented for bookings",
        tags: ["Bookings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Booking ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 405,
                description: "Method not allowed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Update method is not allowed for bookings")
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, $id)
    {
        return ApiResponse::error('Update method is not allowed for bookings', [], 405);
    }

    #[OA\Delete(
        path: "/api/bookings/{id}",
        summary: "Cancel a booking",
        description: "Cancel an existing booking (Users can only cancel their own bookings)",
        tags: ["Bookings"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Booking ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Booking cancelled successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Booking cancelled successfully"),
                        new OA\Property(property: "data", type: "null", example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Booking not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Booking not found")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Unauthorized to cancel this booking",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized to cancel this booking")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated")
                    ]
                )
            )
        ]
    )]
    public function destroy($id)
    {
        try {
            $deleted = $this->bookingService->deleteBooking($id);
            if (!$deleted) {
                return ApiResponse::error('Booking not found', [], 404);
            }
            return ApiResponse::success(null, 'Booking cancelled successfully');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), [], 403);
        }
    }
}
