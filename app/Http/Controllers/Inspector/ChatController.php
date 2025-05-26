<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Display a list of all active conversations.
     */
    public function index()
    {
        // Get all active conversations
        $activeConversations = ChatConversation::waitingForInspector()
            ->with('candidate')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get conversations this inspector is already involved in
        $myConversations = ChatConversation::where('inspector_id', Auth::id())
            ->where('status', 'inspector_joined')
            ->with('candidate')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        // Count unread messages for each conversation
        foreach ($activeConversations as $conversation) {
            $conversation->unread_count = $conversation->messages()
                ->where('is_from_ai', false)
                ->whereNull('read_at')
                ->count();
                
            // Get the last message
            $conversation->last_message = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        foreach ($myConversations as $conversation) {
            $conversation->unread_count = $conversation->messages()
                ->where('user_id', '!=', Auth::id())
                ->where('is_from_ai', false)
                ->whereNull('read_at')
                ->count();
                
            // Get the last message
            $conversation->last_message = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        return view('inspector.chat.index', compact('activeConversations', 'myConversations'));
    }
    
    /**
     * Show a specific conversation.
     */
    public function show(ChatConversation $conversation)
    {
        // If this is a new conversation for this inspector, join it
        if ($conversation->status === 'active' && !$conversation->inspector_id) {
            $conversation->update([
                'inspector_id' => Auth::id(),
                'status' => 'inspector_joined'
            ]);
            
            // Add a system message
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'is_from_ai' => true,
                'message' => 'An inspector has joined the conversation. You\'re now chatting with a real person.',
                'read_at' => now()
            ]);
            
            // Notify the candidate
            Notification::create([
                'user_id' => $conversation->candidate_id,
                'message' => 'An inspector has joined your chat conversation.',
                'type' => Notification::TYPE_SYSTEM,
                'link' => route('candidate.chat.index')
            ]);
        }
        
        // Ensure the inspector has permission to view this conversation
        if ($conversation->status === 'inspector_joined' && $conversation->inspector_id !== Auth::id()) {
            return redirect()->route('inspector.chat.index')
                ->with('error', 'This conversation is already being handled by another inspector.');
        }
        
        // Get messages
        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Mark unread messages as read
        $unreadMessages = $messages->filter(function ($message) {
            return !$message->is_from_ai && $message->user_id !== Auth::id() && is_null($message->read_at);
        });
        
        foreach ($unreadMessages as $message) {
            $message->markAsRead();
        }
        
        $candidate = User::find($conversation->candidate_id);
        
        return view('inspector.chat.show', compact('conversation', 'messages', 'candidate'));
    }
    
    /**
     * Send a message in the conversation.
     */
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        
        // Ensure the inspector has permission to send to this conversation
        if ($conversation->status !== 'inspector_joined' || $conversation->inspector_id !== Auth::id()) {
            return redirect()->route('inspector.chat.index')
                ->with('error', 'You do not have permission to send messages in this conversation.');
        }
        
        // Save the message
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'is_from_ai' => false,
            'message' => $request->message,
        ]);
        
        // Notify the candidate
        Notification::create([
            'user_id' => $conversation->candidate_id,
            'message' => 'You have a new message in your chat conversation.',
            'type' => Notification::TYPE_SYSTEM,
            'link' => route('candidate.chat.index')
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }
        
        return redirect()->route('inspector.chat.show', $conversation);
    }
    
    /**
     * Get new messages (for polling or real-time updates).
     */
    public function getNewMessages(Request $request, ChatConversation $conversation)
    {
        // Ensure the inspector has permission to view this conversation
        if (($conversation->status === 'inspector_joined' && $conversation->inspector_id !== Auth::id()) ||
            ($conversation->status === 'active' && $conversation->inspector_id)) {
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
            if (!$message->is_from_ai && $message->user_id !== Auth::id() && is_null($message->read_at)) {
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
        // Ensure the inspector has permission to close this conversation
        if ($conversation->status !== 'inspector_joined' || $conversation->inspector_id !== Auth::id()) {
            return redirect()->route('inspector.chat.index')
                ->with('error', 'You do not have permission to close this conversation.');
        }
        
        $conversation->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);
        
        // Add a system message
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'is_from_ai' => true,
            'message' => 'This conversation has been closed by the inspector.',
            'read_at' => now()
        ]);
        
        // Notify the candidate
        Notification::create([
            'user_id' => $conversation->candidate_id,
            'message' => 'Your chat conversation has been closed by the inspector.',
            'type' => Notification::TYPE_SYSTEM,
            'link' => route('candidate.chat.index')
        ]);
        
        return redirect()->route('inspector.chat.index')
            ->with('success', 'Chat conversation closed successfully.');
    }
} 