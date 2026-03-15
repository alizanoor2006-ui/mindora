    </div> <!-- end main-content -->
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            // document.getElementById('main-content').classList.toggle('shifted');
        }
        
        // Settings for Audio if we implemented it globally
        const audio = new Audio('../assets/audio/calm-bg.mp3');
        audio.loop = true;
        if(localStorage.getItem('mindora_audio_enabled') === 'true') {
            audio.play().catch(e => console.log('Audio play blocked before interaction'));
        }
    </script>
</body>
</html>
