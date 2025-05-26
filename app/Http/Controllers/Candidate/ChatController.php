<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Display the chat interface.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get or create active conversation
        $conversation = $user->candidateConversations()
            ->where('status', '!=', 'closed')
            ->first();
            
        if (!$conversation) {
            $conversation = $this->aiChatService->createConversation($user);
        }
        
        // Get messages for the conversation
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Mark all unread messages as read
        $unreadMessages = $messages->filter(function ($message) use ($user) {
            return !$message->is_from_ai && $message->user_id !== $user->id && is_null($message->read_at);
        });
        
        foreach ($unreadMessages as $message) {
            $message->markAsRead();
        }
        
        return view('candidate.chat.index', compact('conversation', 'messages'));
    }
    
    /**
     * Send a message in the conversation.
     */
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        
        $user = Auth::user();
        
        // Ensure the user is the candidate in this conversation
        if ($conversation->candidate_id !== $user->id) {
            return redirect()->route('candidate.chat.index')
                ->with('error', 'You do not have access to this conversation.');
        }
        
        // Save the user's message
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'is_from_ai' => false,
            'message' => $request->message,
        ]);
        
        // Process the message with AI if no inspector has joined
        if ($conversation->status === 'active') {
            $aiResponse = $this->aiChatService->processMessage($conversation, $request->message);
            
            if ($aiResponse) {
                $this->aiChatService->saveAiMessage($conversation, $aiResponse);
            }
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        return redirect()->route('candidate.chat.index');
    }
    
    /**
     * Get new messages (for polling or real-time updates).
     */
    public function getNewMessages(Request $request, ChatConversation $conversation)
    {
        $user = Auth::user();
        
        // Ensure the user is the candidate in this conversation
        if ($conversation->candidate_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $lastMessageId = $request->input('last_message_id', 0);
        
        $newMessages = $conversation->messages()
            ->with('user')
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Mark messages as read
        foreach ($newMessages as $message) {
            if (!$message->is_from_ai && $message->user_id !== $user->id && is_null($message->read_at)) {
                $message->markAsRead();
            }
        }
        
        return response()->json([
            'messages' => $newMessages,
            'conversation_status' => $conversation->status
        ]);
    }
    
    /**
     * Close the conversation.
     */
    public function closeConversation(ChatConversation $conversation)
    {
        $user = Auth::user();
        
        // Ensure the user is the candidate in this conversation
        if ($conversation->candidate_id !== $user->id) {
            return redirect()->route('candidate.chat.index')
                ->with('error', 'You do not have access to this conversation.');
        }
        
        $conversation->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);
        
        // Add a system message
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'is_from_ai' => true,
            'message' => 'This conversation has been closed by the candidate.',
            'read_at' => now()
        ]);
        
        return redirect()->route('candidate.chat.index')
            ->with('success', 'Chat conversation closed successfully. You can start a new one at any time.');
    }
} 