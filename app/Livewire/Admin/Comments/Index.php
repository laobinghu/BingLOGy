<?php

namespace App\Livewire\Admin\Comments;

use App\Models\Comment;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('admin.layout')]
    public string $tab = 'pending';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function approve(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved']);
        session()->flash('success', '已批准评论。');
    }

    public function markSpam(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'spam']);
        session()->flash('success', '已标记为垃圾。');
    }

    public function delete(int $id): void
    {
        Comment::findOrFail($id)->delete();
        session()->flash('success', '已删除评论。');
    }

    public function render()
    {
        $query = Comment::query()->with('post')->orderByDesc('created_at');

        match ($this->tab) {
            'pending' => $query->where('status', 'pending'),
            'approved' => $query->where('status', 'approved'),
            'spam' => $query->where('status', 'spam'),
            default => null,
        };

        $counts = [
            'all' => Comment::count(),
            'pending' => Comment::where('status', 'pending')->count(),
            'approved' => Comment::where('status', 'approved')->count(),
            'spam' => Comment::where('status', 'spam')->count(),
        ];

        return view('admin.comments.index', [
            'comments' => $query->get(),
            'counts' => $counts,
        ]);
    }
}
