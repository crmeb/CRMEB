<?php
use think\facade\Route;

Route::group('aiagent', function () {
    Route::get('options', [\modules\aiagent\controller\admin\AiAgentController::class, 'options'])
        ->option(['real_name' => 'AI 对话配置选项']);
    Route::get('conversations', [\modules\aiagent\controller\admin\AiAgentController::class, 'conversations'])
        ->option(['real_name' => 'AI 对话会话列表']);
    Route::get('messages/:conversation_id', [\modules\aiagent\controller\admin\AiAgentController::class, 'messages'])
        ->option(['real_name' => 'AI Agent 会话消息']);
    Route::delete('conversation/:id', [\modules\aiagent\controller\admin\AiAgentController::class, 'deleteConversation'])
        ->option(['real_name' => '删除 AI Agent 会话']);
    Route::put('conversation/:id', [\modules\aiagent\controller\admin\AiAgentController::class, 'updateConversation'])
        ->option(['real_name' => '修改 AI Agent 会话（标题/置顶）']);
    Route::post('chat', [\modules\aiagent\controller\admin\AiAgentController::class, 'chat'])
        ->option(['real_name' => 'AI Agent 对话']);
    Route::post('chat_stream', [\modules\aiagent\controller\admin\AiAgentController::class, 'chatStream'])
        ->option(['real_name' => 'AI Agent 流式对话']);
    Route::post('confirm_stream', [\modules\aiagent\controller\admin\AiAgentController::class, 'confirmStream'])
        ->option(['real_name' => 'AI Agent 待确认操作处理']);
    // ===== AI 自动化任务 =====
    Route::get('tasks', [\modules\aiagent\controller\admin\TaskController::class, 'index'])
        ->option(['real_name' => 'AI 自动化任务列表']);
    Route::post('task', [\modules\aiagent\controller\admin\TaskController::class, 'save'])
        ->option(['real_name' => '创建 AI 自动化任务']);
    Route::get('task/:id', [\modules\aiagent\controller\admin\TaskController::class, 'read'])
        ->option(['real_name' => 'AI 自动化任务详情']);
    Route::put('task/:id', [\modules\aiagent\controller\admin\TaskController::class, 'save'])
        ->option(['real_name' => '编辑 AI 自动化任务']);
    Route::delete('task/:id', [\modules\aiagent\controller\admin\TaskController::class, 'delete'])
        ->option(['real_name' => '删除 AI 自动化任务']);
    Route::put('task/:id/status', [\modules\aiagent\controller\admin\TaskController::class, 'status'])
        ->option(['real_name' => '修改 AI 自动化任务状态']);
    Route::post('task/:id/run', [\modules\aiagent\controller\admin\TaskController::class, 'trigger'])
        ->option(['real_name' => '立即执行 AI 自动化任务']);
    Route::get('task/:id/runs', [\modules\aiagent\controller\admin\TaskController::class, 'runs'])
        ->option(['real_name' => 'AI 自动化任务运行列表']);
    Route::get('task_runs', [\modules\aiagent\controller\admin\TaskController::class, 'runIndex'])
        ->option(['real_name' => 'AI 自动化任务全量运行记录']);
    Route::get('task_run/:id', [\modules\aiagent\controller\admin\TaskController::class, 'runDetail'])
        ->option(['real_name' => 'AI 自动化任务运行详情']);
    Route::get('mcp_servers', [\modules\aiagent\controller\admin\McpServerController::class, 'index'])
        ->option(['real_name' => 'MCP Server 列表']);
    Route::post('mcp_server', [\modules\aiagent\controller\admin\McpServerController::class, 'save'])
        ->option(['real_name' => '创建 MCP Server']);
    Route::get('mcp_server/:id', [\modules\aiagent\controller\admin\McpServerController::class, 'read'])
        ->option(['real_name' => 'MCP Server 详情']);
    Route::put('mcp_server/:id', [\modules\aiagent\controller\admin\McpServerController::class, 'save'])
        ->option(['real_name' => '编辑 MCP Server']);
    Route::delete('mcp_server/:id', [\modules\aiagent\controller\admin\McpServerController::class, 'delete'])
        ->option(['real_name' => '删除 MCP Server']);
    Route::put('mcp_server/:id/status', [\modules\aiagent\controller\admin\McpServerController::class, 'status'])
        ->option(['real_name' => '修改 MCP Server 状态']);
    Route::post('mcp_server/:id/sync', [\modules\aiagent\controller\admin\McpServerController::class, 'sync'])
        ->option(['real_name' => '同步 MCP Server 工具']);
    Route::post('mcp_server/:id/test', [\modules\aiagent\controller\admin\McpServerController::class, 'test'])
        ->option(['real_name' => '测试 MCP Server 连接']);
    Route::get('skills', [\modules\aiagent\controller\admin\SkillController::class, 'index'])
        ->option(['real_name' => 'Skill 列表']);
    Route::post('skill', [\modules\aiagent\controller\admin\SkillController::class, 'save'])
        ->option(['real_name' => '创建 Skill']);
    // 全局默认路由变量规则为 [\w\.]+（不含连字符），skill key 允许连字符，需显式声明
    Route::pattern(['key' => '[A-Za-z0-9_-]+']);
    Route::get('skill/:key', [\modules\aiagent\controller\admin\SkillController::class, 'read'])
        ->option(['real_name' => 'Skill 详情']);
    Route::put('skill/:key', [\modules\aiagent\controller\admin\SkillController::class, 'update'])
        ->option(['real_name' => '编辑 Skill']);
    Route::delete('skill/:key', [\modules\aiagent\controller\admin\SkillController::class, 'delete'])
        ->option(['real_name' => '删除 Skill']);
    Route::put('skill/:key/status', [\modules\aiagent\controller\admin\SkillController::class, 'status'])
        ->option(['real_name' => '修改 Skill 状态']);
    // Skill 附属资料（文件名含中文/点号，统一经查询参数 file 传递，避免路由变量编码问题）
    Route::get('skill/:key/attachments', [\modules\aiagent\controller\admin\SkillController::class, 'attachmentList'])
        ->option(['real_name' => 'Skill 附属资料列表']);
    Route::get('skill/:key/attachment', [\modules\aiagent\controller\admin\SkillController::class, 'attachmentRead'])
        ->option(['real_name' => 'Skill 附属资料详情']);
    Route::post('skill/:key/attachment', [\modules\aiagent\controller\admin\SkillController::class, 'attachmentSave'])
        ->option(['real_name' => '保存 Skill 附属资料']);
    Route::delete('skill/:key/attachment', [\modules\aiagent\controller\admin\SkillController::class, 'attachmentDelete'])
        ->option(['real_name' => '删除 Skill 附属资料']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class,
])->option(['mark' => 'aiagent', 'mark_name' => 'AI Agent']);
