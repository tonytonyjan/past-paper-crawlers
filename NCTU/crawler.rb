require 'nokogiri'
require 'open-uri'
require 'fileutils'
require 'json'
past_papers = []
FileUtils.mkdir_p 'files'
url = "http://www.lib.nctu.edu.tw/exame/"
file_counter = 0
Nokogiri::HTML(open(url)).css('#subcate ul').first.css('a').each{|a|
  Nokogiri::HTML(open(a[:href])).css('#exame ul li a').each{|a|
    department = a[:title].strip
    program = nil
    department, program = $1, $2 if department =~ /(.*[班系所])(.*組)/
    Nokogiri::HTML(open(a[:href])).css('.right').each{|right|
      subject = right.css('.title').text.strip
      right.css('.list a').each{|a|
        file_url = a[:href]
        year = a[:title].to_i + 1911
        begin
          open(file_url){|f|
            file_name = (f.meta['content-disposition'] && f.meta['content-disposition'][/filename="(.*)"/, 1]) || File.basename(file_url)
            file_path = File.join('files', "#{file_counter += 1}-#{file_name}")
            File.write(file_path, f.read) unless File.exist?(file_path)
            past_paper = {file_paths: []}
            past_paper[:school] = '國立交通大學'
            past_paper[:department], past_paper[:program] = department, program
            past_paper[:subject] = subject
            past_paper[:file_paths] << file_path
            past_paper[:year] = year
            past_paper[:exam_type] = "入學考"
            p past_paper
            past_papers << past_paper
          }
        rescue
          $stderr.puts "ERROR: #{$!}, #{$@.first}, #{department}, #{subject}, #{file_url}"
        end
      }
    }
  }
}
File.write('past_papers.json', past_papers.to_json)