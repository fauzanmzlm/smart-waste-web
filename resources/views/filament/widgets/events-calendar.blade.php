<x-filament-widgets::widget>
    <x-filament::section>
        <div class="mb-4 flex justify-between items-center">
            <h2 class="text-xl font-bold">Cleanup Events Calendar</h2>
            
            <div class="flex space-x-2">
                <x-filament::button 
                    color="{{ $filter === 'all' ? 'primary' : 'gray' }}"
                    wire:click="changeFilter('all')"
                >
                    All Events
                </x-filament::button>
                
                <x-filament::button 
                    color="{{ $filter === 'upcoming' ? 'primary' : 'gray' }}"
                    wire:click="changeFilter('upcoming')"
                >
                    Upcoming
                </x-filament::button>
                
                <x-filament::button 
                    color="{{ $filter === 'past' ? 'primary' : 'gray' }}"
                    wire:click="changeFilter('past')"
                >
                    Past
                </x-filament::button>
            </div>
        </div>
        
        <div id="calendar-{{ $this->getId() }}" class="w-full h-96"></div>
        
        {{-- Include FullCalendar scripts and styles --}}
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
        
        <style>
            body {
                color: #333;
            }
            .fc-event {
                cursor: pointer;
            }
            .fc-event-time {
                display: none;
            }
            .fc-daygrid-event-dot {
                display: none;
            }
        </style>
        
        <script>
            // Initialize calendar when component loads
            document.addEventListener('livewire:init', function() {
                initializeCalendar();
                
                // Set up event listener for filter changes
                Livewire.on('refreshEvents', () => {
                    refreshCalendarEvents();
                });
            });
            
            // Initialize the calendar
            function initializeCalendar() {
                const calendarEl = document.getElementById('calendar-{{ $this->getId() }}');
                
                if (!calendarEl) return;
                
                // Create calendar object and store it in a global variable so we can access it later
                window.calendar{{ $this->getId() }} = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listMonth'
                    },
                    events: @json($this->getEvents()),
                    eventClick: function(info) {
                        window.location.href = info.event.url;
                    },
                    eventContent: function(arg) {
                        return {
                            html: `<div class="p-1">
                                <div class="font-bold text-xs">${arg.event.title}</div>
                                <div class="text-xs">${arg.event.extendedProps.time || ''}</div>
                            </div>`
                        };
                    }
                });
                
                // Render the calendar
                window.calendar{{ $this->getId() }}.render();
            }
            
            // Refresh calendar events when filter changes
            function refreshCalendarEvents() {
                // Get the Livewire component instance
                const component = Livewire.find('{{ $this->getId() }}');
                
                if (!component) return;
                
                // Get fresh events data from the component
                const events = component.getEvents();
                
                // If calendar exists, update events
                if (window.calendar{{ $this->getId() }}) {
                    // Remove existing events
                    window.calendar{{ $this->getId() }}.getEvents().forEach(event => event.remove());
                    
                    // Add new events
                    if (events && events.length) {
                        events.forEach(event => window.calendar{{ $this->getId() }}.addEvent(event));
                    }
                }
            }
        </script>
    </x-filament::section>
</x-filament-widgets::widget>