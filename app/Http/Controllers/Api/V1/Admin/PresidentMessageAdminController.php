<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PresidentMessageResource;
use App\Models\PresidentMessage;
use App\Support\Audit;
use Illuminate\Http\Request;

class PresidentMessageAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureRole($request);

        $messages = PresidentMessage::with('photo')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        return PresidentMessageResource::collection($messages);
    }

    public function show(Request $request, int $id)
    {
        $this->ensureRole($request);

        $message = PresidentMessage::with('photo')->findOrFail($id);

        return new PresidentMessageResource($message);
    }

    public function store(Request $request)
    {
        $this->ensureRole($request);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'intro' => 'nullable|string',
            'content' => 'required|string',
            'president_name' => 'required|string|max:255',
            'president_title' => 'nullable|string|max:100',
            'mandate_period' => 'nullable|string|max:50',
            'photo_id' => 'nullable|exists:media,id',
            'is_active' => 'boolean',
        ]);

        $message = PresidentMessage::create($data);

        // If set as active, deactivate others
        if ($message->is_active) {
            $message->activate();
        }

        Audit::log($request, 'president_message.create', 'PresidentMessage', $message->id, [
            'title' => $message->title,
        ]);

        return new PresidentMessageResource($message->load('photo'));
    }

    public function update(Request $request, int $id)
    {
        $this->ensureRole($request);

        $message = PresidentMessage::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'intro' => 'nullable|string',
            'content' => 'sometimes|required|string',
            'president_name' => 'sometimes|required|string|max:255',
            'president_title' => 'nullable|string|max:100',
            'mandate_period' => 'nullable|string|max:50',
            'photo_id' => 'nullable|exists:media,id',
            'is_active' => 'boolean',
        ]);

        $message->update($data);

        // If set as active, deactivate others
        if ($message->is_active) {
            $message->activate();
        }

        Audit::log($request, 'president_message.update', 'PresidentMessage', $message->id, [
            'title' => $message->title,
        ]);

        return new PresidentMessageResource($message->load('photo'));
    }

    public function destroy(Request $request, int $id)
    {
        $this->ensureRole($request);

        $message = PresidentMessage::findOrFail($id);

        Audit::log($request, 'president_message.delete', 'PresidentMessage', $message->id, [
            'title' => $message->title,
        ]);

        $message->delete();

        return response()->json(['data' => true]);
    }

    public function activate(Request $request, int $id)
    {
        $this->ensureRole($request);

        $message = PresidentMessage::findOrFail($id);
        $message->activate();

        Audit::log($request, 'president_message.activate', 'PresidentMessage', $message->id, [
            'title' => $message->title,
        ]);

        return response()->json(['data' => true, 'message' => 'Message activé']);
    }

    private function ensureRole(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['SuperAdmin', 'Validateur']), 403);
    }
}
