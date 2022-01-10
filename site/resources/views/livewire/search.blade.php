<form>
<table>
  <tr>
    <td>
      <label>fName</label>
      <select class="form-select" wire:model="model.fName">
     <option>Select fName</option>
     @foreach($authors_config['fields'] as $a)
       <option value="{{$a['fName']}}">{{$a['fName']}}</option>
     @endforeach
    </select>
  </td>
  <td>
    <div class="form-group">
        <label>Value</label>
        <input type="text" class="form-control" wire:model="model.fValue">
    </div
</td>
<td>
    <button wire:click.prevent="search()" class="btn btn-success">Search</button>
</td>
</tr>
</table>
</form>
