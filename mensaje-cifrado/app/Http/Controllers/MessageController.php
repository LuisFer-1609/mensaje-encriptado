<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $folder = $request->query('folder', 'inbox');

        $query = Message::query();

        $statusFilter = $request->query('status');
        $search = $request->query('search');

        if ($search) {
            // Búsqueda global: Enviados O Recibidos que coincidan con el asunto
            $query->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhere('recipient_id', $userId);
            })->where('subject', 'like', "%{$search}%");
        } else {
            // Sin búsqueda: Filtrar por carpeta
            if ($folder === 'sent') {
                // Enviados: Filtro simple por mi ID como remitente
                $query->where('sender_id', $userId);
            } else {
                // Recibidos: Yo soy el destinatario
                $query->where('recipient_id', $userId);

                // FILTRO DE ESTATUS SOLO EN RECIBIDOS
                if ($statusFilter === 'read') {
                    $query->where('status', 'read');
                } elseif ($statusFilter === 'unread') {
                    $query->where('status', '!=', 'read');
                }
            }
        }

        $messages = $query->with(['sender', 'recipient'])
            ->latest()
            ->get()
            ->map(function ($message) use ($userId) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'body' => $message->body, // Cifrado
                    'created_at' => $message->created_at,
                    'is_sent' => $message->sender_id === $userId,
                    'is_received' => $message->recipient_id === $userId,
                    'other_party' => $message->sender_id === $userId ? ($message->recipient ? $message->recipient->email : 'Desconocido') : ($message->sender ? $message->sender->email : 'Desconocido'),
                ];
            });

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'to' => 'required|email|exists:users,email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string', // Este es el body encriptado
        ], [
            'to.exists' => 'El correo destinatario no está registrado en nuestro sistema.',
            'to.required' => 'El campo Para es obligatorio.',
            'to.email' => 'Debes ingresar un correo electrónico válido.'
        ]);

        $recipient = User::where('email', $request->to)->firstOrFail();

        $message = Message::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $recipient->id,
            'subject' => $request->subject,
            'body' => $request->message,
            'status' => 0,
        ]);

        // Cargar relaciones para que el evento lleve los nombres
        $message->load(['sender', 'recipient']);

        event(new MessageSent($message));

        return response()->json([
            'message' => 'Mensaje enviado correctamente.',
            'data' => [
                'id' => $message->id,
                'subject' => $message->subject,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'sender_id' => $message->sender_id,
                'recipient_id' => $message->recipient_id,
                // relations para el frontend
                'sender' => $message->sender,
                'recipient' => $message->recipient
            ]
        ]);
    }

   public function checkEmail(Request $request)
    {
        // Validamos que el formato sea de correo
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'public_key' => $user->public_key,
                'status' => 'valid'
            ]);
        }

        return response()->json([
            'exists' => false,
            'status' => 'not-found'
        ]);
    }
    public function markAsRead($id)
    {
        // Buscamos el mensaje asegurándonos de que el usuario autenticado sea el destinatario
        $message = Message::where('id', $id)
            ->where('recipient_id', auth()->id())
            ->first();

        if (!$message) {
            return response()->json([
                'error' => 'No tienes permiso para marcar este mensaje o no existe.'
            ], 403);
        }

        $message->update(['status' => 'read']);

        return response()->json([
            'message' => 'Mensaje marcado como leído.',
            'status' => $message->status
        ]);
    }
}
