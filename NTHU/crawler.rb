require 'nokogiri'
require 'open-uri'
require 'fileutils'
require 'json'
past_papers = []
FileUtils.mkdir_p 'files'
url = "http://www.lib.nthu.edu.tw/library/department/ref/exam/"
file_counter = 0
Nokogiri::HTML(open(url)).css('div#wrapper > div#cwrp.clearfix > div > ul > li > ul > li > a').each{|a|
  department = a.text.strip
  url2 = URI.join(url, a[:href])
  Nokogiri::HTML(open(url2)).css('div#wrapper>div#cwrp.clearfix>table.listview>tbody>tr')[1..-1].each{|tr|
    year = tr.css('td:nth-child(1)').text.to_i + 1911
    tr.css('td:nth-child(2)>ul>li').each{|li|
      links = li.css('a')
      program = li.child.text[/(.*)：/, 1]
      links.each{|a|
        subject = a.text.strip
        file_url = URI.join(url2, a[:href]).to_s
        open(file_url){|f|
          file_name = (f.meta['content-disposition'] && f.meta['content-disposition'][/filename="(.*)"/, 1]) || File.basename(file_url)
          file_path = File.join('files', "#{file_counter += 1}-#{file_name}")
          File.write(file_path, f.read) unless File.exist?(file_path)
          past_paper = {file_paths: []}
          past_paper[:school] = '國立清華大學'
          past_paper[:department], past_paper[:program] = department, program
          past_paper[:subject] = subject
          past_paper[:file_paths] << file_path
          past_paper[:year] = year
          past_paper[:exam_type] = "入學考"
          p past_paper
          past_papers << past_paper
        } rescue $stderr.puts "ERROR: #{$!}, #{$@.first}, #{department}, #{subject}, #{file_url}"
      }
    }
  } rescue $stderr.puts "ERROR: #{$!}, #{$@.first}, #{url}"
}

File.write('past_papers.json', past_papers.to_json)