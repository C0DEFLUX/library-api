<?php

namespace App\Http\Controllers;

use App\Models\Books;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PharIo\Manifest\Library;

class LibraryController extends Controller
{
    function index(): \Illuminate\Http\JsonResponse
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

            //Check if text fields are empty
            if(empty($title) || empty($desc) || empty($author) || empty($image)) {

                //Return text error message
                return response()->json([
                    'message' => [
                        'title' => 'Please add a title!',
                        'desc' => 'Please add a description!',
                        'author' => 'Please add an author!',
                        'image' => 'Please add a image!'
                    ],
                    'status' => 403
                ]);
            }
            //Check title length
            if(strlen($title) > 50) {
                return response()->json([
                    'message' => "Title can't exceed 100 characters!",
                    'status' => 403
                ]);
            }
            //Check desc length
            if(strlen($desc) > 3000) {
                return response()->json([
                    'message' => "Description can't exceed 3000 characters!",
                    'status' => 403
                ]);
            }
            //Check author length
            if(strlen($author) > 50) {
                return response()->json([
                    'message' => "Author field can't exceed 50 characters",
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

    function update(Request $request ,$id) {
        //Init data
        $title = htmlspecialchars(trim($request->input('title')));
        $author = htmlspecialchars(trim($request->input('author')));
        $desc = htmlspecialchars(trim($request->input('description')));

        //Check if text fields are empty
        if(empty($title) || empty($desc) || empty($author)) {

            //Return text error message
            return response()->json([
                'message' => [
                    'title' => 'Please add a title!',
                    'desc' => 'Please add a description!',
                    'author' => 'Please add an author!',
                    'image' => 'Please add a image!'
                ],
                'status' => 403
            ]);
        }
        //Check title length
        if(strlen($title) > 50) {
            return response()->json([
                'message' => "Title can't exceed 100 characters!",
                'status' => 403
            ]);
        }
        //Check desc length
        if(strlen($desc) > 3000) {
            return response()->json([
                'message' => "Description can't exceed 3000 characters!",
                'status' => 403
            ]);
        }
        //Check author length
        if(strlen($author) > 50) {
            return response()->json([
                'message' => "Author field can't exceed 50 characters",
                'status' => 403
            ]);
        }
        if($request->hasFile('image')) {

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

}


