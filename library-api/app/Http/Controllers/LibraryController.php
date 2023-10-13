<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PharIo\Manifest\Library;

class LibraryController extends Controller
{
    function index(): JsonResponse
    {
        $data = Books::all();

        return response()->json($data);
    }

    function create(Request $request)
    {
            //Init data
            $title = htmlspecialchars(trim($request->input('title')));
            $author = htmlspecialchars(trim($request->input('author')));
            $desc = htmlspecialchars(trim($request->input('description')));
            $image = $request->file('image');

            $err_arr = [
                'title' => '',
                'desc' => '',
                'author' => '',
                'image' => '',
            ];

            if(empty($title)) {
                $err_arr['title'] = 'Please add a title!';
            }
            if(empty($desc)) {
                $err_arr['desc'] = 'Please add a description!';
            }
            if(empty($author)) {
                $err_arr['author'] = 'Please add an author!';
            }
            if(empty($image)) {
                $err_arr['image'] = 'Please add a image!';
            }

            //Check if errors are empty
            if(!empty($err_arr['title']) || !empty($err_arr['desc']) || !empty($err_arr['author']) || !empty($err_arr['image'])) {

                //Return text error message
                return response()->json([
                    'error' => $err_arr,
                    'status' => 403
                ]);
            }

            $lenght_err_arr = [
                'title' => '',
                'desc' => '',
                'author' => '',
                'image' => ''
            ];

            //Set a max length for text fields
            if(strlen($title) > 50) {
                $lenght_err_arr['title'] = "Title can't exceed 50 characters!";
            }
            if(strlen($desc) > 3000) {
                $lenght_err_arr['desc'] = "Description can't exceed 3000 characters!";
            }
            if(strlen($author) > 50) {
                $lenght_err_arr['author'] = "Author field can't exceed 50 characters";
            }

            if(!empty($lenght_err_arr['title']) || !empty($lenght_err_arr['desc']) || !empty($lenght_err_arr['author']) || !empty($lenght_err_arr['image'])) {
                return response()->json([
                    'error' => $lenght_err_arr,
                    'status' => 403
                ]);
            }
            //Get original name
            $image_name = $image->getClientOriginalName();
            //Add time to original name to make sure images never match
            $final_image_name = date('His') . $image_name;
            //Image path
            $path = $request->file('image')->storeAs('images', $final_image_name,'public');
            //Full path of image for db
            $image_url = asset('storage/'. $path);
            //Make data arr
            $data = [
                'title' => $title,
                'author' => $author,
                'description' => $desc,
                'image' => $image_url
            ];

            //Insert data into db
            Books::create($data);
            //Return success message
            return response()->json([
                'message' => 'New book added successfully!',
                'status' => 200
            ]);
    }
    function destroy($id) {

        //Find book by id
        $book = Books::find($id);
        //Make Absolute URL from db into a usable URL
        $book_image = str_replace(asset('storage/'), 'public', $book->image);
        //Delete image from storage
        unlink(Storage::path($book_image));
        //Delete book info from db
        $book->delete();

        return response()->json([
            'message' => 'Book removed successfully!',
            'status' => 200,
        ]);
    }

     function update(Request $request ,$id): JsonResponse
     {
        //Init data
        $title = htmlspecialchars(trim($request->input('title')));
        $author = htmlspecialchars(trim($request->input('author')));
        $desc = htmlspecialchars(trim($request->input('description')));

         $err_arr = [
             'title' => '',
             'desc' => '',
             'author' => '',
             'image' => '',
         ];

         if(empty($title)) {
             $err_arr['title'] = 'Please add a title!';
         }
         if(empty($desc)) {
             $err_arr['desc'] = 'Please add a description!';
         }
         if(empty($author)) {
             $err_arr['author'] = 'Please add an author!';
         }

         //Check if errors are empty
         if(!empty($err_arr['title']) || !empty($err_arr['desc']) || !empty($err_arr['author'])) {

             //Return text error message
             return response()->json([
                 'error' => $err_arr,
                 'status' => 403
             ]);
         }

         $lenght_err_arr = [
             'title' => '',
             'desc' => '',
             'author' => '',
             'image' => ''
         ];

         //Set a max length for text fields
         if(strlen($title) > 50) {
             $lenght_err_arr['title'] = "Title can't exceed 50 characters!";
         }
         if(strlen($desc) > 3000) {
             $lenght_err_arr['desc'] = "Description can't exceed 3000 characters!";
         }
         if(strlen($author) > 50) {
             $lenght_err_arr['author'] = "Author field can't exceed 50 characters";
         }

         if(!empty($lenght_err_arr['title']) || !empty($lenght_err_arr['desc']) || !empty($lenght_err_arr['author']) || !empty($lenght_err_arr['image'])) {
             return response()->json([
                 'error' => $lenght_err_arr,
                 'status' => 403
             ]);
         }
        if($request->hasFile('image')) {

            //Find book by id
            $book = Books::find($id);
            //Make Absolute URL from db into a usable URL
            $book_image = str_replace(asset('storage/'), 'public', $book->image);
            //Delete image from storage
            unlink(Storage::path($book_image));

            $image = $request->file('image');
            //Get original name
            $image_name = $image->getClientOriginalName();
            //Add time to original name to make sure images never match
            $final_image_name = date('His') . $image_name;
            //Image path
            $path = $request->file('image')->storeAs('images', $final_image_name,'public');
            //Full path of image for db
            $image_url = asset('storage/'. $path);
            //Make data arr
            $data = [
                'title' => $title,
                'author' => $author,
                'description' => $desc,
                'image' => $image_url
            ];
            Books::where('id', $id)->update($data);

            return response()->json([
                'message' => 'Book updated successfully!'
            ]);
        }

        $data = [
            'title' => $title,
            'author' => $author,
            'description' => $desc,
        ];
        Books::where('id', $id)->update($data);

        return response()->json([
            'message' => 'Book updated successfully!',
            'status' => 200
        ]);

    }

    public function bookById($id)
    {
        $data = Books::where('id', $id)->first();

        return response()->json($data);
    }

    public function reserveBook(Request $request)
    {
        $id = $request->input('id');
        $uid = $request->input('uid');
        $time =  $request->input('time');

        $maxDate = Carbon::now()->addYear();
        $today = Carbon::today();
        $time = Carbon::parse($time);

        if($time->greaterThanOrEqualTo($maxDate)) {
            return response()->json([
                'message' => 'Reservation date is not valid!',
                'status' => 403
            ]);
        }

        if($time->lessThan($today)) {
            return response()->json([
                'message' => 'Reservation date is not valid!',
                'status' => 403
            ]);
        }

        $user = Users::where('id', '=', $uid)->first();

        DB::table('books')
            ->where('id', $id)
            ->update([
                'user' => $user->username,
                'reserved' => 1,
                'reserve_time' => $time
            ]);

        return response()->json([
            'message' => 'Book reserved successfully!',
            'status' => 200
        ]);
    }

    public function returnBook(Request $request): JsonResponse
    {
        $uid = $request->input('id');

        DB::table('books')
            ->where('id', $uid)
            ->update([
                'user' => null,
                'reserved' => 0,
                'reserve_time' => null
            ]);

        return response()->json([
            'message' => 'Book returned successfully!',
            'status' => 200
        ]);

    }

    public function reservedBooksById($user): JsonResponse
    {
        $data = Books::where('user', $user)->get();

        return response()->json($data);
    }

}


