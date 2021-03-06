<?php

namespace FSR\Http\Controllers\Donor;

use FSR\Cso;
use FSR\Admin;
use FSR\Comment;
use FSR\Listing;
use FSR\ListingOffer;
use FSR\Notifications;
use FSR\Notifications\DonorToCsoComment;
use FSR\Notifications\DonorToAdminComment;
use FSR\Notifications\DonorToVolunteerComment;
use FSR\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class MyAcceptedListingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:donor');
    }

    /**
     * Show a shigle listing offer
     * @param Request
     * @param int $listing_offer_id
     * @return void
     */
    public function single_listing_offer(Request $request, $listing_offer_id = null)
    {
        $listing_offer = ListingOffer::where('offer_status', 'active')
                                    ->whereHas('listing', function ($query) {
                                        $query->where('donor_id', Auth::user()->id)
                                              ->where('listing_status', 'active');
                                    })->find($listing_offer_id);

        $comments = Comment::where('listing_offer_id', $listing_offer_id)
                            ->where('status', 'active')
                            ->orderBy('created_at', 'ASC')->get();
        $selected_filter = $this->get_selected_filter($listing_offer);
        if ($listing_offer) {
            return view('donor.my_accepted_listings')->with([
            'listing_offer' => $listing_offer,
            'comments' => $comments,
            'selected_filter' => $selected_filter,
          ]);
        } else {
            //not ok, show error page
        }
    }

    /**
     * Handles post to this page
     *
     * @param  Request  $request
     * @param  int  $listing_offer_id
     * @return \Illuminate\Http\Response
     */
    public function single_listing_offer_post(Request $request, int $listing_offer_id = null)
    {
        //catch input-comment post
        if ($request->has('submit-comment')) {
            $comment = $this->create_comment($request->all(), $listing_offer_id);

            return back();
        }

        if ($request->has('delete-comment')) {
            $comment = $this->delete_comment($request->all());
            return back()->with('status', "Коментарот е избришан!");
        }

        if ($request->has('edit-comment')) {
            $comment = $this->edit_comment($request->all());
            return back()->with('status', "Коментарот е изменет!");
        }

        //za drugite:
        // if $request->has('edit-comment-9')
    }

    /**
     * Create a new comment instance after a valid input.
     *
     * @param  array  $data
     * @param  int  $listing_offer_id
     * @return \FSR\Comment
     */
    protected function create_comment(array $data, int $listing_offer_id)
    {
        $comment_text = $data['comment'];
        $listing_offer = ListingOffer::find($listing_offer_id);
        $cso = $listing_offer->cso;
        $volunteer = $listing_offer->volunteer;
        $other_comments = Comment::where('status', 'active')->where('listing_offer_id', $listing_offer_id)->get();
        //send notification to the cso
        $cso->notify(new DonorToCsoComment($listing_offer, $comment_text, $other_comments));

        //send notification to the volunteer
        if ($cso->email != $volunteer->email) {
            $volunteer->notify(new DonorToVolunteerComment($listing_offer, $comment_text, $other_comments));
        }

        //send to master_admin(s)
        $master_admins = Admin::where('master_admin', 1)->get();
        Notification::send($master_admins, new DonorToAdminComment($listing_offer, $comment_text, $other_comments));

        //find all regular admins that commented, and send them all
        $admin_comments = Comment::where('status', 'active')
                  ->where('listing_offer_id', $listing_offer_id)
                  ->where('sender_type', 'admin')
                  ->get();
        if ($admin_comments) {
            $admin_ids=array();
            $regular_admins = Admin::where('master_admin', 0);
            foreach ($regular_admins as $admin) {
                foreach ($admin_comments as $admin_comment) {
                    if ($admin_comment->user_id == $admin->id) {
                        if (!in_array($admin->id, $admin_ids)) {
                            $admin_ids[]=$admin->id;
                        }
                    }
                }
            }
            foreach ($admin_ids as $admin_id) {
                Admin::find($admin_id)->notify(new DonorToAdminComment($listing_offer, $comment_text, $other_comments));
            }
        }

        return Comment::create([
            'listing_offer_id' => $listing_offer_id,
            'user_id' => Auth::user()->id,
            'sender_type' => Auth::user()->type(),
            'text' => $comment_text,
        ]);
    }


    /**
     * Mark the selected comment as deleted
     *
     * @param  array  $data
     * @return \FSR\Comment
     */
    protected function delete_comment(array $data)
    {
        $comment = Comment::find($data['comment_id']);
        $comment->status = 'deleted';
        $comment->save();
        return $comment;
    }

    /**
     * Edit the selected comment text
     *
     * @param  array  $data
     * @return \FSR\Comment
     */
    protected function edit_comment(array $data)
    {
        $comment = Comment::find($data['comment_id']);
        $comment->text = $data['edit_comment_text'];
        $comment->save();
        return $comment;
    }

    private function get_selected_filter($listing_offer)
    {
        if ($listing_offer->listing->listing_status == 'active') {
            if ($listing_offer->listing->date_expires < Carbon::now()->format('Y-m-d H:i')) {
                return 'past';
            } else {
                return 'active';
            }
        } else {
            return 'past';
        }
    }
}
