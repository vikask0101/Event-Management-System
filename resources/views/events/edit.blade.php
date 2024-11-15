@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
    <div class="container mt-4">
        <h1>Edit Event</h1>

        <form id="editEventForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ encrypt_data($event->id) }}">

            <div class="mb-3">
                <label for="title" class="form-label">Event Title</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $event->title }}">
                <span id="titleError" class="text-danger"></span>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Event Description</label>
                <textarea class="form-control" id="description" name="description">{{ $event->description }}</textarea>
                <span id="descriptionError" class="text-danger"></span>
            </div>

            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="{{ $event->event_date->format('Y-m-d') }}">
                <span id="event_dateError" class="text-danger"></span>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Event Location</label>
                <input type="text" class="form-control" id="location" name="location" value="{{ $event->location }}">
                <span id="locationError" class="text-danger"></span>
            </div>

            <div id="ticketTypesContainer" class="mb-3">
                <label class="form-label">Ticket Types</label>
                @foreach($event->ticketTypes as $index => $ticketType)
                    <div id="ticketType{{ $index }}" class="ticketType mb-3" data-index="{{ $index }}">
                        <input type="text" class="form-control mb-2" name="ticket_types[{{ $index }}][name]" value="{{ $ticketType->name }}" placeholder="Ticket Name (e.g., VIP)">
                        <span id="nameError-{{ $index }}" class="text-danger"></span>

                        <input type="number" class="form-control mb-2" name="ticket_types[{{ $index }}][price]" value="{{ $ticketType->price }}" placeholder="Price">
                        <span id="priceError-{{ $index }}" class="text-danger"></span>

                        <input type="number" class="form-control mb-2" name="ticket_types[{{ $index }}][quantity]" value="{{ $ticketType->quantity }}" placeholder="Quantity">
                        <span id="quantityError-{{ $index }}" class="text-danger"></span>
                        @if ($index > 0)
                        <button type="button" class="btn btn-danger remove-ticket-btn">Remove</button>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="button" id="addTicketTypeBtn" class="btn btn-secondary mb-3">Add More Ticket Type</button>
            <br />
            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>

        <div id="message" class="mt-3 text-center"></div>
    </div>
@endsection

@push('scripts')
    <script>
        let ticketCount = {{ count($event->ticketTypes) }};

        $('#addTicketTypeBtn').on('click', function() {
            const newTicketTypeHtml = `
                <div id="ticketType${ticketCount}" class="ticketType mb-3" data-index="${ticketCount}">
                    <input type="text" class="form-control mb-2" name="ticket_types[${ticketCount}][name]" placeholder="Ticket Name (e.g., VIP)">
                    <span id="nameError-${ticketCount}" class="text-danger"></span>

                    <input type="number" class="form-control mb-2" name="ticket_types[${ticketCount}][price]" placeholder="Price">
                    <span id="priceError-${ticketCount}" class="text-danger"></span>

                    <input type="number" class="form-control mb-2" name="ticket_types[${ticketCount}][quantity]" placeholder="Quantity">
                    <span id="quantityError-${ticketCount}" class="text-danger"></span>

                    <button type="button" class="btn btn-danger remove-ticket-btn">Remove</button>
                </div>
            `;
            $('#ticketTypesContainer').append(newTicketTypeHtml);
            ticketCount++;
        });

        // Function to remove ticket type field
                // Function to remove ticket type field
        $(document).on('click', '.remove-ticket-btn', function() {
            $(this).closest('.ticketType').remove();
        });


        $('#editEventForm').on('submit', function(e) {
            e.preventDefault();

            $('.text-danger').empty();

            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route('events.update', $event->id) }}',
                method: 'PUT',
                data: formData,
                success: function(data) {
                    if (data.success) {
                        $('#message').html('<div class="alert alert-success">' + data.message +
                            '</div>');
                        // Reset form fields (optional)
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;

                        // Display errors for ticket types
                        $.each(errors, function(field, errorMessages) {
                            if (field.startsWith('ticket_types')) {
                                const index = field.split('.')[1];
                                const fieldType = field.split('.')[2];

                                $(`#ticketTypesContainer .ticketType:nth-child(${parseInt(index)})`)
                                    .attr('data-index', index);

                                if (fieldType === 'name') {
                                    $(`#nameError-${index}`).html(errorMessages.join('<br>'));
                                } else if (fieldType === 'price') {
                                    $(`#priceError-${index}`).html(errorMessages.join('<br>'));
                                } else if (fieldType === 'quantity') {
                                    $(`#quantityError-${index}`).html(errorMessages.join(
                                        '<br>'));
                                }

                            } else {
                                // errors (non-ticket-related)
                                $('#' + field + 'Error').html(errorMessages.join('<br>'));
                            }
                        });
                    } else {
                        console.error('Unexpected error:', error);
                    }
                }
            });
        });
    </script>
@endpush
