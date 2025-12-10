<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Courses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    protected $courseService;

    public function __construct(CourseServices $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Create a new booking
     *
     * @param int $courseId
     * @return Booking|null
     */
    public function createBooking(int $courseId): ?Booking
    {
        return DB::transaction(function () use ($courseId) {
            // Check if course exists and has available seats
            $course = Courses::find($courseId);

            if (!$course) {
                throw new \Exception('Course not found');
            }

            if ($course->available_seats <= 0) {
                throw new \Exception('No available seats for this course');
            }

            // Check if user already booked this course
            $existingBooking = Booking::where('user_id', Auth::id())
                ->where('course_id', $courseId)
                ->first();

            if ($existingBooking) {
                throw new \Exception('You have already booked this course');
            }

            // Reduce available seats
            if (!$this->courseService->reduceSeats($courseId, 1)) {
                throw new \Exception('Failed to book course');
            }

            // Create booking
            return Booking::create([
                'user_id' => Auth::id(),
                'course_id' => $courseId
            ]);
        });
    }

    /**
     * Get a booking by ID
     *
     * @param int $id
     * @return Booking|null
     */
    public function getBooking( $id): ?Booking
    {
        return Booking::with(['user', 'course'])->find($id);
    }

    /**
     * Get all bookings
     *
     * @return Collection
     */
    public function getAllBookings(): Collection
    {
        return Booking::with(['user', 'course'])->get();
    }

    /**
     * Get user's bookings
     *
     * @param int|null $userId
     * @return Collection
     */
    public function getUserBookings(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        return Booking::with(['course'])
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Get bookings for a specific course
     *
     * @param int $courseId
     * @return Collection
     */
    public function getCourseBookings(int $courseId): Collection
    {
        return Booking::with(['user'])
            ->where('course_id', $courseId)
            ->get();
    }

    /**
     * Delete a booking (Cancel booking)
     *
     * @param int $id
     * @return bool
     */
    public function deleteBooking(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $booking = Booking::find($id);

            if (!$booking) {
                return false;
            }

            // Check if the authenticated user owns this booking
            if ($booking->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized to cancel this booking');
            }

            // Increase available seats back
            $this->courseService->increaseSeats($booking->course_id, 1);

            // Delete booking
            return $booking->delete();
        });
    }

    /**
     * Check if user has booked a course
     *
     * @param int $courseId
     * @param int|null $userId
     * @return bool
     */
    public function hasUserBookedCourse(int $courseId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        return Booking::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }
}
