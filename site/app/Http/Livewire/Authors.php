<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Author;

class Authors extends Component
{
    public $posts, $title, $body, $post_id;
    public $model = [];
    public $updateMode = false;

    public function render()
    {
       // $this->authors = Author::all()->toArray();
       $this->authors = Author::all();
       $this->authors_config = Author::$rainbowTableIndexConfig;
       //dd($this->authors);
       return view('livewire.authors');
    }

    public function delete($id)
    {
      Log::channel('stderr')->info('delete............', [] );
    }

    public function edit($id)
    {
      Log::channel('stderr')->info('edit............', [] );
    }


    public function search()
    {
      Log::channel('stderr')->info('search..>>>>', [$this->model] );
      if(
        (!empty($this->model['fName']))
        &&
        (!empty($this->model['fValue']))
        )
      {
        $fName = $this->model['fName'];
        $fValue = $this->model['fValue'];
        $this->authors = Author::where($fName, 'LIKE', '%' . $fValue . '%')->get();
        Log::channel('stderr')->info('### returned -->', [$fName, $fValue, $this->authors] );
      }
      else
      {
          Log::channel('stderr')->info('@@@@@ ERROR: search data void............', [$this->model] );
      }
      // $arr2 = $a::select('id')->where($fName, 'LIKE', '%' . $token_2_search . '%')->get()->toArray();

    }
}
