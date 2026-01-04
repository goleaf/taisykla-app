<?php

namespace Tests\Feature;

use App\Livewire\KnowledgeBase\ArticleList;
use App\Livewire\KnowledgeBase\Home;
use App\Livewire\KnowledgeBase\Index as KnowledgeBaseManage;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KnowledgeBaseRefactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_base_home_renders_correctly()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::KNOWLEDGE_BASE_VIEW);

        $this->actingAs($user)
            ->get(route('knowledge-base.index'))
            ->assertStatus(200)
            ->assertSeeLivewire(Home::class)
            ->assertSee('How can we help you?');
    }

    public function test_knowledge_base_search_renders_correctly()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::KNOWLEDGE_BASE_VIEW);

        $this->actingAs($user)
            ->get(route('knowledge-base.search'))
            ->assertStatus(200)
            ->assertSeeLivewire(ArticleList::class)
            ->assertSee('Search articles...');
    }

    public function test_knowledge_base_manage_is_protected()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::KNOWLEDGE_BASE_VIEW);
        // User does NOT have KNOWLEDGE_BASE_MANAGE

        $this->actingAs($user)
            ->get(route('knowledge-base.manage'))
            ->assertForbidden();
    }

    public function test_manager_can_access_manage_page()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionCatalog::KNOWLEDGE_BASE_MANAGE);

        $this->actingAs($user)
            ->get(route('knowledge-base.manage'))
            ->assertStatus(200)
            ->assertSeeLivewire(KnowledgeBaseManage::class);
    }
}
