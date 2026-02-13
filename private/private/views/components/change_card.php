<div class="change_card" data-index="<?= $index ?>">
    <div class="timeline_dot <?= strtolower($log->action) ?>"></div>
    
    <div class="change_header">
        <div class="change_meta">
            <div class="change_time">
                <?= date('d.m.Y', strtotime($log->created_at)) ?>
            </div>
            <div class="change_date">
                <?= date('H:i', strtotime($log->created_at)) ?>
            </div>
            <span class="action_badge <?= $actionClass ?>">
                <?= $actionLabels[$log->action] ?? $log->action ?>
            </span>
        </div>
        <div class="user_info">
            <div class="user_avatar">
                <? if(!empty($admin->image)): ?>
                    <img src="<?= $admin->image ?>" alt="">
                <? else: ?>
                    <img src="/private/src/images/no_admin.png" alt="">
                <? endif; ?>
            </div>
            <div class="user_details">
                <div class="user_name">
                    <?= $adminDisplay ?>
                </div>
                <?php if (!empty($log->admin_ip)): ?>
                    <div class="user_ip">
                        <?= $log->admin_ip ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($log->action !== 'PUBLICATION'): ?>
    <div class="change_content">
        <div class="field_name">
            <?= $fieldLabel ?>
        </div>
        <div class="field_values">
            <?php if ($log->action === 'INSERT'): ?>
                <div class="value_box new_value">
                    <?= $newValue ?>
                </div>
            <?php elseif ($log->action === 'UPDATE'): ?>
                <div class="value_change">
                    <div class="value_box old_value">
                        <?= $oldValue ?>
                    </div>
                    <div class="arrow_icon">→</div>
                    <div class="value_box new_value">
                        <?= $newValue ?>
                    </div>
                </div>
            <?php elseif ($log->action === 'DELETE'): ?>
                <div class="value_box old_value">
                    <?= $oldValue ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
</div>