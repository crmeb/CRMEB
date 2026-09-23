import { reactive, ref } from 'vue';

/**
 * AI 对话流式请求的状态与 SSE 事件处理（chat_stream 与 confirm_stream 共用，支持链式确认）。
 *
 * @param {Object} options
 * @param {Object} options.messages 消息列表（ref，切换会话会整体替换数组，内部始终读取 .value）
 * @param {Object} options.conversationId 当前会话 ID（ref，start 事件回写）
 * @param {Function} options.onScroll 消息区滚动到底部回调
 */
export function useChatStream({ messages, conversationId, onScroll }) {
  /** 是否有流式请求进行中（对话与确认操作互斥） */
  const chatting = ref(false);

  /**
   * 新建流式回复占位对象。
   * 必须用 reactive 包装：push 进响应式数组后，局部变量持有的是原始对象（非代理），
   * 直接修改原始对象不会触发视图更新（流式输出会卡在"正在思考…"）。
   */
  function newReply() {
    return reactive({
      role: 'assistant',
      content: '',
      reasoning: '',
      reasoningOpen: false,
      streaming: true,
      // 回复时间（秒级时间戳）：历史消息取后端 create_time，实时回复在流式结束时补记，字段名保持一致
      create_time: 0,
      tool_calls: [],
      skills: [],
      followups: [],
    });
  }

  /** 流式结束：收起思维链与占位状态，无有效内容的占位（如确认中断后的空回复）直接移除 */
  function finishReply(reply) {
    reply.streaming = false;
    reply.reasoningOpen = false;
    // 记录回复完成时间，供底部操作区展示（历史消息由后端 create_time 提供）
    reply.create_time = Math.floor(Date.now() / 1000);
    const list = messages.value;
    const index = list.indexOf(reply);
    if (
      index > -1 &&
      !reply.content &&
      !reply.reasoning &&
      !(reply.tool_calls || []).length &&
      !(reply.skills || []).length
    ) {
      list.splice(index, 1);
    }
    chatting.value = false;
    onScroll();
  }

  /** SSE 事件统一处理 */
  function handleStreamEvent(event, reply) {
    switch (event.event) {
      case 'start':
        conversationId.value = event.conversation_id || conversationId.value;
        break;
      case 'skills':
        // 已加载的 Skill 标签：开始时为预载（显式选择的），按需加载的随对话动态推送更新（整体替换语义）
        reply.skills = event.skills || [];
        break;
      case 'reasoning':
        // 仅首个思维链增量自动展开，用户可随时手动收起
        if (!reply.reasoning) reply.reasoningOpen = true;
        reply.reasoning += event.text || '';
        onScroll();
        break;
      case 'delta':
        reply.content += event.text || '';
        onScroll();
        break;
      case 'replace':
        // 工具调用前言被识别为中间过程，或模型答案经结果校正后，用最终内容原位替换。
        reply.content = event.content || '';
        onScroll();
        break;
      case 'tool':
        // 工具调用前的中间前言不作为最终回答保留，转入思维链保存（并保持展开），避免正文突然消失的突兀感
        if (reply.content) {
          reply.reasoning += (reply.reasoning ? '\n' : '') + reply.content;
          reply.reasoningOpen = true;
        }
        reply.content = '';
        reply.tool_calls.push({ name: event.name });
        onScroll();
        break;
      case 'confirm':
        // 需用户交互的工具被拦截：中间前言同样转入思维链，渲染交互卡片，本次对话中断等待用户操作
        if (reply.content) {
          reply.reasoning += (reply.reasoning ? '\n' : '') + reply.content;
          reply.reasoningOpen = true;
        }
        reply.content = '';
        reply.streaming = false;
        messages.value.push({
          role: 'confirm',
          messageId: event.message_id,
          toolName: event.tool_name,
          arguments: event.arguments || {},
          interaction: event.interaction || 'execute',
          // 后端下发的中文展示信息（模型提供或工具定义兜底），缺失时卡片降级显示工具名
          display: event.display || null,
          status: 'pending',
        });
        onScroll();
        break;
      case 'followups':
        // 回答完成后的推荐追问
        reply.followups = event.questions || [];
        onScroll();
        break;
      case 'done':
        reply.skills = event.skills || [];
        // 后端权威工具清单兜底同步：即使流式中某条 tool 事件缺失/乱序，结束时标签也与实际调用一致
        if (Array.isArray(event.tool_calls) && event.tool_calls.length) {
          reply.tool_calls = event.tool_calls.map((item) => ({ name: item?.name || '' }));
        }
        break;
    }
  }

  return { chatting, newReply, finishReply, handleStreamEvent };
}
