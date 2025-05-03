<?php

namespace App\Filament\Widgets;

use App\Models\CleanupEvent;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class EventsCalendar extends Widget
{
    protected static string $view = 'filament.widgets.events-calendar';
    
    protected int|string|array $columnSpan = 'full';
    
    public ?string $filter = 'upcoming';
    
    // Livewire lifecycle hook
    public function mount(): void
    {
        // Initial setup
    }
    
    // Get the events based on the current filter
    public function getEvents(): array
    {
        $query = CleanupEvent::query();
        
        // Apply filter
        if ($this->filter === 'upcoming') {
            $query->where('date', '>=', now()->format('Y-m-d'));
        } elseif ($this->filter === 'past') {
            $query->where('date', '<', now()->format('Y-m-d'));
        }
        
        // Get the events
        $events = $query->orderBy('date', 'asc')->get();
        
        // Format for calendar
        $calendarEvents = [];
        
        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start' => Carbon::parse($event->date)->format('Y-m-d'),
                'url' => route('filament.admin.resources.cleanup-events.edit', ['record' => $event->id]),
                'extendedProps' => [
                    'location' => $event->location,
                    'time' => $event->time,
                    'organizer' => $event->organizer,
                    'image' => $event->image,
                ],
            ];
        }
        
        return $calendarEvents;
    }
    
    // Change the filter and refresh the calendar
    public function changeFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->dispatch('refreshEvents');
    }
}