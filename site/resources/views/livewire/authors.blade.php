<div>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if($updateMode)
        @include('livewire.update')
    @else
        @include('livewire.search')
    @endif

    <table class="table table-bordered mt-5">
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Name*</th>
                <th>Card no.</th>
                <th>Address</th>
                <th>Role</th>
                <th>Role enc</th>
                <th width="150px">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($authors as $author)
            <tr>
                <td>{{ $author->id }}</td>
                <td>{{ $author->name }}</td>
                <td>{{ $author->name_enc }}</td>
                <td>{{ $author->card_number }}</td>
                <td>{{ $author->address }}</td>
                <td>{{ $author->role }}</td>
                <td>{{ $author->role_enc }}</td>
                <td>
                <button wire:click="edit({{ $author->id }})" class="btn btn-primary btn-sm">Edit</button>
                    <button wire:click="delete({{ $author->id }})" class="btn btn-danger btn-sm">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
