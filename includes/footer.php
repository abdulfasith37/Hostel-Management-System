<footer class="site-footer">
    <p>&copy; 2026 University of Vavuniya. All rights reserved.</p>
    <button type="button" class="btn-team" onclick="openTeamModal()">Meet the Team</button>
</footer>

<!-- Team Modal Popup -->
<div id="teamModal" class="team-modal-backdrop" onclick="closeOnBackdrop(event)">
    <div class="team-modal">
        <div class="team-modal-header">
            <h3>Meet the Team</h3>
            <button type="button" class="team-modal-close" onclick="closeTeamModal()">&times;</button>
        </div>
        <div class="team-modal-body">
            <!-- Lead Developer -->
            <div class="lead-card">
                <span class="lead-badge">Lead Developer</span>
                <div class="member-name" style="font-size: 16px;">Najeem Abdul Fasith</div>
                <div class="member-reg">Registration No: <strong>2023ICT107</strong></div>
                <div class="lead-socials">
                    <!-- Email -->
                    <a href="mailto:abdulfasith373@gmai.com" class="social-btn" title="Email" target="_blank">
                        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                    </a>
                    <!-- LinkedIn -->
                    <a href="https://linkedin.com/in/abdulfasith" class="social-btn" title="LinkedIn" target="_blank">
                        <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.45a1.6 1.6 0 1 0 1.6 1.6 1.6 1.6 0 0 0-1.6-1.6z"/></svg>
                    </a>
                    <!-- GitHub -->
                    <a href="https://github.com/abdulfasith37" class="social-btn" title="GitHub" target="_blank">
                        <svg viewBox="0 0 24 24"><path d="M12 2A10 10 0 0 0 2 12c0 4.42 2.87 8.17 6.84 9.5.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.87 1.52 2.34 1.07 2.91.83.1-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.92 0-1.11.38-2 1.03-2.71-.1-.25-.45-1.29.1-2.64 0 0 .84-.27 2.75 1.02.79-.22 1.65-.33 2.5-.33.85 0 1.71.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.35.2 2.39.1 2.64.65.71 1.03 1.6 1.03 2.71 0 3.82-2.34 4.66-4.57 4.91.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Other Team Members -->
            <h4 style="font-size: 13px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 10px;">Team Members</h4>
            <div class="members-list">
                <div class="member-item">
                    <div class="member-name">MFF. Fasla</div>
                    <div class="member-reg">2023ICT053</div>
                </div>
                <div class="member-item">
                    <div class="member-name">WAS. Pabodha</div>
                    <div class="member-reg">2023ICT047</div>
                </div>
                <div class="member-item">
                    <div class="member-name">RPDTD. Jayathilaka</div>
                    <div class="member-reg">2023ICT052</div>
                </div>
                <div class="member-item">
                    <div class="member-name">Jeyarasa Kavippiriya</div>
                    <div class="member-reg">2023ICT108</div>
                </div>
                <div class="member-item">
                    <div class="member-name">RMAR. Bandara</div>
                    <div class="member-reg">2023ICT046</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openTeamModal() {
    document.getElementById('teamModal').style.display = 'flex';
}
function closeTeamModal() {
    document.getElementById('teamModal').style.display = 'none';
}
function closeOnBackdrop(e) {
    if (e.target.id === 'teamModal') {
        closeTeamModal();
    }
}
</script>